<?php

namespace App\Controller;

/**
 * Controller pour la synthèse vocale (Text-to-Speech) utilisant VoiceRSS.
 * Version gratuite sans carte bancaire requise.
 */
use App\Entity\Course;
use App\Service\FileScannerService;
use App\Service\VoiceRssService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class VoiceRssController extends AbstractController
{
    public function __construct(
        private FileScannerService $fileScannerService,
        private VoiceRssService $voiceRssService,
        #[Autowire('%kernel.project_dir%/public/uploads/audio')] private string $audioDir
    ) {}

    #[Route('/course/{id}/speech', name: 'app_course_speech', methods: ['POST'])]
    public function generateSpeech(Course $course): JsonResponse
    {
        $filename = $course->getCourseFile();
        if (!$filename) {
            return $this->json(['error' => 'Aucun fichier attaché à ce cours.'], Response::HTTP_BAD_REQUEST);
        }

        // Créer le dossier audio si inexistant
        if (!is_dir($this->audioDir)) {
            mkdir($this->audioDir, 0777, true);
        }

        // Cache basé sur le nom du fichier
        $cacheFilename = md5($filename) . '.mp3';
        $cachePath = $this->audioDir . DIRECTORY_SEPARATOR . $cacheFilename;

        if (file_exists($cachePath)) {
            return $this->json([
                'audioUrl' => '/uploads/audio/' . $cacheFilename,
                'cached' => true
            ]);
        }

        // Extraction du texte
        $text = $this->fileScannerService->extractText($filename);
        if (empty(trim($text))) {
            return $this->json(['error' => 'Impossible d\'extraire le texte du fichier.'], Response::HTTP_BAD_REQUEST);
        }

        // Limite VoiceRSS standard (très large, on garde 10 000 pour la sécurité)
        if (strlen($text) > 10000) {
            $text = substr($text, 0, 9900) . "... [Contenu tronqué]";
        }

        try {
            // Optimization for professional pacing
            $optimizedText = $this->voiceRssService->optimizeTextForSpeech($text, $course->getName());
            
            $audioContent = $this->voiceRssService->generateSpeech($optimizedText);
            file_put_contents($cachePath, $audioContent);

            return $this->json([
                'audioUrl' => '/uploads/audio/' . $cacheFilename,
                'cached' => false
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
