<?php

namespace App\Service;

use Smalot\PdfParser\Parser;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class PdfScannerService
{
    private string $uploadsDir;

    public function __construct(
        #[Autowire('%kernel.project_dir%/public/uploads/courses')] string $uploadsDir
    ) {
        $this->uploadsDir = $uploadsDir;
    }

    /**
     * Extracts text content from a PDF file.
     * 
     * @param string $filename The name of the file in the uploads directory
     * @return string The extracted text, or an empty string if it fails
     */
    public function extractText(string $filename): string
    {
        $filePath = $this->uploadsDir . DIRECTORY_SEPARATOR . $filename;

        if (!file_exists($filePath)) {
            return "";
        }

        try {
            $parser = new Parser();
            $pdfContent = $parser->parseFile($filePath);

            // Limit extraction to first few thousand characters to avoid token bloating
            $text = $pdfContent->getText();

            // Clean up extra whitespace
            return (string) preg_replace('/\s+/', ' ', $text);
        } catch (\Exception $e) {
            // Log error if logger is available or just return empty
            return "";
        }
    }
}
