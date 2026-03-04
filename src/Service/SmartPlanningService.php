<?php

namespace App\Service;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\HttpKernel\KernelInterface;

class SmartPlanningService
{
    private string $projectDir;

    public function __construct(
        KernelInterface $kernel,
        private string $pythonExe
    ) {
        $this->projectDir = $kernel->getProjectDir();
    }

    /**
     * Executes the Python script to analyze motivation and optimize tasks.
     *
     * @param array{
     *   energy: int,
     *   stress: int,
     *   sleep_quality: int,
     *   mood_text: string,
     *   date: string
     * } $userState
     * @param list<array{
     *   id: int,
     *   title: string,
     *   difficulty: int|null,
     *   initial_duration: int|null
     * }> $tasks
     * @return array<string, mixed> Result from Python service
     */
    public function analyze(array $userState, array $tasks): array
    {
        $inputData = json_encode([
            'user_state' => $userState,
            'tasks' => $tasks
        ]);
        if ($inputData === false) {
            throw new \RuntimeException('Failed to encode input data for Python service.');
        }

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
        if (!is_array($result)) {
            throw new \RuntimeException('Unexpected non-array output from Python service.');
        }

        return $result;
    }
}
