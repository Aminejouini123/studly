<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\SmartPlanningService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\KernelInterface;

final class SmartPlanningServiceTest extends TestCase
{
    public function testAnalyzeReturnsValidPlanningArray(): void
    {
        $fakePython = $this->createFakePythonExecutable();

        $kernel = $this->createMock(KernelInterface::class);
        $kernel->method('getProjectDir')->willReturn(sys_get_temp_dir());

        $service = new SmartPlanningService($kernel, $fakePython);

        $result = $service->analyze(
            [
                'energy' => 7,
                'stress' => 3,
                'sleep_quality' => 8,
                'mood_text' => 'focused',
                'date' => '2026-03-04',
            ],
            [
                [
                    'id' => 1,
                    'title' => 'Math revision',
                    'difficulty' => 2,
                    'initial_duration' => 60,
                ],
            ]
        );

        $this->assertIsArray($result);
        $this->assertArrayHasKey('status', $result);
        $this->assertSame('success', $result['status']);
        $this->assertArrayHasKey('schedule', $result);
        $this->assertIsArray($result['schedule']);
    }

    private function createFakePythonExecutable(): string
    {
        $tmpDir = sys_get_temp_dir();

        if (\PHP_OS_FAMILY === 'Windows') {
            $path = $tmpDir . DIRECTORY_SEPARATOR . 'fake_python_tm.bat';
            file_put_contents($path, "@echo off\r\necho {\"status\":\"success\",\"schedule\":[{\"id\":1,\"duration\":25}]}\r\n");
            return $path;
        }

        $path = $tmpDir . DIRECTORY_SEPARATOR . 'fake_python_tm.sh';
        file_put_contents($path, "#!/bin/sh\necho '{\"status\":\"success\",\"schedule\":[{\"id\":1,\"duration\":25}]}'\n");
        @chmod($path, 0755);

        return $path;
    }
}

