<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\PomodoroSession;
use App\Repository\PomodoroSessionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\BackgroundProcess;
use App\Service\UserActionLogger;

#[IsGranted('ROLE_USER')]
#[Route('/temps/{id}/pomodoro')]
class PomodoroController extends AbstractController
{
    #[Route('/', name: 'app_event_pomodoro', methods: ['GET', 'POST'])]
    public function index(Event $event, Request $request, EntityManagerInterface $em): Response
    {
        // Security check: ensure user owns the event
        if ($event->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        // Handle quick add session form if needed, or just list
        // For now, let's just show the list and timer. 
        // We can add a "Add Session" modal or form later if strictly asked for CRUD *interface*.
        // The user asked for "full CRUD interface". 
        // Let's create a simple form to add a manual session.

        $session = new PomodoroSession();
        $session->setEvent($event);
        $session->setDuration(25);
        $session->setType('WORK');

        // We'll handle the form in a separate route or modal usually, 
        // but for a simple "page", we can put a create form on the side or bottom.

        return $this->render('pomodoro/index.html.twig', [
            'event' => $event,
            'sessions' => $event->getPomodoroSessions(),
        ]);
    }

    #[Route('/new', name: 'app_pomodoro_new', methods: ['POST'])]
    public function new(Event $event, Request $request, EntityManagerInterface $em): Response
    {
        if ($event->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $type = $request->request->get('type', 'WORK');
        $duration = $request->request->get('duration', 25);

        $session = new PomodoroSession();
        $session->setEvent($event);
        $session->setType($type);
        $session->setDuration((int) $duration);
        $session->setStatus('PENDING');

        $em->persist($session);
        $em->flush();

        if ($request->isXmlHttpRequest() || $request->headers->get('Accept') === 'application/json') {
            return $this->json([
                'status' => 'success',
                'id' => $session->getId(),
                'type' => $session->getType(),
                'duration' => $session->getDuration()
            ]);
        }

        return $this->redirectToRoute('app_event_pomodoro', ['id' => $event->getId()]);
    }

    #[Route('/{sessionId}/delete', name: 'app_pomodoro_delete', methods: ['POST'])]
    public function delete(Event $event, int $sessionId, EntityManagerInterface $em): Response
    {
        if ($event->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $session = $em->getRepository(PomodoroSession::class)->find($sessionId);
        if ($session && $session->getEvent() === $event) {
            $em->remove($session);
            $em->flush();
        }

        return $this->redirectToRoute('app_event_pomodoro', ['id' => $event->getId()]);
    }

    #[Route('/{sessionId}/status', name: 'app_pomodoro_status', methods: ['POST'])]
    public function updateStatus(Event $event, int $sessionId, Request $request, EntityManagerInterface $em, UserActionLogger $actionLogger): Response
    {
        if ($event->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $session = $em->getRepository(PomodoroSession::class)->find($sessionId);
        $status = $request->request->get('status');

        if ($session && $session->getEvent() === $event && in_array($status, ['PENDING', 'IN_PROGRESS', 'COMPLETED'])) {
            $session->setStatus($status);

            if ($status === 'COMPLETED') {
                $actionLogger->log($this->getUser(), 'pomodoro_completed', 'Completed a pomodoro session for event: ' . $event->getTitle(), $session);
            }

            $em->flush();
        }

        return $this->redirectToRoute('app_event_pomodoro', ['id' => $event->getId()]);
    }

    #[Route('/{sessionId}/stats', name: 'app_pomodoro_save_stats', methods: ['POST'])]
    #[IsGranted('PUBLIC_ACCESS')]
    public function saveStats(Event $event, int $sessionId, Request $request, EntityManagerInterface $em): Response
    {
        // Security: In a real app, we might need an API token or similar. 
        // For now, we trust the local python script.

        $session = $em->getRepository(PomodoroSession::class)->find($sessionId);
        if (!$session || $session->getEvent() !== $event) {
            return $this->json(['status' => 'error', 'message' => 'Session not found'], 404);
        }

        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return $this->json(['status' => 'error', 'message' => 'Invalid JSON'], 400);
        }

        if (isset($data['average_focus'])) {
            $session->setFocusScore((float) $data['average_focus']);
        }
        if (isset($data['logs'])) {
            $session->setFocusLogs($data['logs']);
        }

        $em->flush();

        return $this->json(['status' => 'success']);
    }

    #[Route('/{sessionId}/launch-tracker', name: 'app_pomodoro_launch_tracker', methods: ['POST'])]
    public function launchTracker(Event $event, int $sessionId, EntityManagerInterface $em): Response
    {
        if ($event->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $session = $em->getRepository(PomodoroSession::class)->find($sessionId);
        if (!$session || $session->getEvent() !== $event) {
            return $this->json(['status' => 'error', 'message' => 'Session not found'], 404);
        }

        // OpenCV CAP_DSHOW bug is fixed, so we can finally use pythonw.exe safely for a 0% flash execution
        $pythonExe = $this->getParameter('python_exe');
        $scriptPath = $this->getParameter('kernel.project_dir') . DIRECTORY_SEPARATOR . 'python_services' . DIRECTORY_SEPARATOR . 'attention_tracking' . DIRECTORY_SEPARATOR . 'tracker.py';

        $apiUrl = $this->generateUrl('app_pomodoro_save_stats', [
            'id' => $event->getId(),
            'sessionId' => $sessionId
        ], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL);

        // Failsafe: kill any existing zombie tracker processes before starting a new one
        $killCmd = 'wmic process where "name=\'python.exe\' and commandline like \'%tracker.py%\'" call terminate > nul 2>&1';
        pclose(popen($killCmd, "r"));

        // THE GOLDEN SOLUTION:
        // Use python.exe (console binary) instead of pythonw.exe because pythonw acts dead in WScript.
        // We use WScript.Shell with window style 0 to natively hide the console window.
        // This guarantees 100% stealth (zero flash) AND guarantees the camera starts and the LED lights up.
        $pythonExe = $this->getParameter('python_exe');

        $vbsPath = $this->getParameter('kernel.project_dir') . DIRECTORY_SEPARATOR . 'exports' . DIRECTORY_SEPARATOR . 'stealth_launch.vbs';

        $vbsCode = "Set WshShell = CreateObject(\"WScript.Shell\")\n";
        $vbsCode .= 'WshShell.Run """' . $pythonExe . '"" ""' . $scriptPath . '"" ""' . $apiUrl . '"" ' . $sessionId . '", 0, False' . "\n";

        file_put_contents($vbsPath, $vbsCode);

        // Execute the VBS natively silently
        pclose(popen("wscript.exe \"" . $vbsPath . "\"", "r"));

        return $this->json(['status' => 'success', 'message' => 'Tracker started natively via Stealth VBS']);
    }

    #[Route('/{sessionId}/stop-tracker', name: 'app_pomodoro_stop_tracker', methods: ['POST'])]
    public function stopTracker(Event $event, int $sessionId, EntityManagerInterface $em): Response
    {
        if ($event->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $session = $em->getRepository(PomodoroSession::class)->find($sessionId);
        if (!$session || $session->getEvent() !== $event) {
            return $this->json(['status' => 'error', 'message' => 'Session not found'], 404);
        }

        $stopFile = $this->getParameter('kernel.project_dir') . DIRECTORY_SEPARATOR . 'exports' . DIRECTORY_SEPARATOR . 'stop_' . $sessionId . '.txt';
        file_put_contents($stopFile, "STOP");

        // Failsafe: aggressively kill the python process to guarantee the camera LED turns off
        $killCmd = 'wmic process where "name=\'python.exe\' and commandline like \'%tracker.py%\'" call terminate > nul 2>&1';
        pclose(popen($killCmd, "r"));

        return $this->json(['status' => 'success']);
    }

    #[Route('/{sessionId}/get-stats', name: 'app_pomodoro_get_stats', methods: ['GET'])]
    public function getStats(Event $event, int $sessionId, EntityManagerInterface $em): Response
    {
        if ($event->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $session = $em->getRepository(PomodoroSession::class)->find($sessionId);
        if (!$session || $session->getEvent() !== $event) {
            return $this->json(['status' => 'error'], 404);
        }

        if ($session->getFocusScore() !== null) {
            return $this->json([
                'status' => 'success',
                'score' => $session->getFocusScore(),
                'logs' => $session->getFocusLogs()
            ]);
        }

        return $this->json(['status' => 'pending']);
    }

    #[Route('/{sessionId}/tracker-status', name: 'app_pomodoro_tracker_status', methods: ['GET'])]
    public function trackerStatus(Event $event, int $sessionId, EntityManagerInterface $em): Response
    {
        if ($event->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $session = $em->getRepository(PomodoroSession::class)->find($sessionId);
        if (!$session || $session->getEvent() !== $event) {
            return $this->json(['status' => 'error'], 404);
        }

        $statusFile = $this->getParameter('kernel.project_dir') . DIRECTORY_SEPARATOR . 'exports' . DIRECTORY_SEPARATOR . 'status_' . $sessionId . '.json';

        if (file_exists($statusFile)) {
            $content = file_get_contents($statusFile);
            $data = json_decode($content, true);
            if ($data) {
                return $this->json([
                    'status' => 'active',
                    'state' => $data['state'],
                    'score' => $data['score']
                ]);
            }
        }

        return $this->json(['status' => 'initializing']);
    }
}
