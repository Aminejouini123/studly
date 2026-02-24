<?php

namespace App\Service;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\HttpKernel\KernelInterface;

class SmartPlanningService
{
    private string $projectDir;
    private string $pythonExe = 'C:\Users\ghali\anaconda3\python.exe'; // From verification step

    public function __construct(KernelInterface $kernel)
    {
        $this->projectDir = $kernel->getProjectDir();
    }

    /**
     * Executes the Python script to analyze motivation and optimize tasks.
     *
     * @param array $userState ['energy', 'stress', 'sleep_quality', 'mood_text', 'date']
     * @param array $tasks [['id', 'title', 'difficulty', 'initial_duration']]
     * @return array Result from Python service
     */
    public function analyze(array $userState, array $tasks): array
    {
        $inputData = json_encode([
            'user_state' => $userState,
            'tasks' => $tasks
        ]);

        $scriptPath = $this->projectDir . DIRECTORY_SEPARATOR . 'python_services' . DIRECTORY_SEPARATOR . 'time_management' . DIRECTORY_SEPARATOR . 'main.py';

        $process = new Process([$this->pythonExe, $scriptPath]);
        $process->setInput($inputData);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $output = $process->getOutput();
        $result = json_decode($output, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Invalid JSON output from Python service: ' . $output);
        }

        return $result;
    }
}
