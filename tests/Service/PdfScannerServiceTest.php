<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\PdfScannerService;
use PHPUnit\Framework\TestCase;

final class PdfScannerServiceTest extends TestCase
{
    public function testExtractTextReturnsEmptyStringWhenFileDoesNotExist(): void
    {
        $service = new PdfScannerService(sys_get_temp_dir());

        $result = $service->extractText('file-that-does-not-exist.pdf');

        $this->assertSame('', $result);
    }

    public function testExtractTextReturnsEmptyStringWhenFileIsInvalidPdf(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'bad_pdf_');
        $this->assertNotFalse($tmpFile);

        $filePath = $tmpFile . '.pdf';
        rename($tmpFile, $filePath);
        file_put_contents($filePath, 'this is not a real pdf');

        try {
            $service = new PdfScannerService(sys_get_temp_dir());
            $result = $service->extractText(basename($filePath));
            $this->assertSame('', $result);
        } finally {
            @unlink($filePath);
        }
    }
}

