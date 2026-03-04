<?php

namespace App\Tests\Service;

use App\Service\FaceRecognitionService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class FaceRecognitionServiceTest extends TestCase
{
    private HttpClientInterface&MockObject $client;
    private LoggerInterface&MockObject $logger;
    private FaceRecognitionService $service;
    private string $faceApiUrl = 'http://api.example.com';

    protected function setUp(): void
    {
        $this->client = $this->createMock(HttpClientInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->service = new FaceRecognitionService($this->client, $this->faceApiUrl, $this->logger);
    }

    public function testRegisterWithValidData(): void
    {
        $userId = 1;
        $descriptor = [0.1, 0.2, 0.3];
        $expectedResponse = ['status' => 'success'];

        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')->willReturn($expectedResponse);

        $this->client->expects($this->once())
            ->method('request')
            ->with('POST', $this->faceApiUrl . '/face/register', [
                'json' => [
                    'user_id' => $userId,
                    'descriptor' => $descriptor
                ]
            ])
            ->willReturn($response);

        $result = $this->service->register($userId, $descriptor);
        $this->assertEquals($expectedResponse, $result);
    }

    public function testRegisterWithInvalidUserId(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('L\'ID de l\'utilisateur doit être positif');

        $this->service->register(0, [0.1, 0.2]);
    }

    public function testRegisterWithEmptyDescriptor(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le descripteur facial est obligatoire');

        $this->service->register(1, []);
    }

    public function testLoginWithValidData(): void
    {
        $descriptor = [0.1, 0.2, 0.3];
        $expectedResponse = ['user_id' => 1];

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('toArray')->willReturn($expectedResponse);

        $this->client->expects($this->once())
            ->method('request')
            ->with('POST', $this->faceApiUrl . '/face/login', [
                'json' => [
                    'descriptor' => $descriptor
                ]
            ])
            ->willReturn($response);

        $result = $this->service->login($descriptor);
        $this->assertEquals($expectedResponse, $result);
    }

    public function testLoginWithEmptyDescriptor(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le descripteur facial est obligatoire');

        $this->service->login([]);
    }

    public function testLoginReturnsNullOnFailure(): void
    {
        $descriptor = [0.1, 0.2, 0.3];

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(401);

        $this->client->method('request')->willReturn($response);

        $result = $this->service->login($descriptor);
        $this->assertNull($result);
    }
}
