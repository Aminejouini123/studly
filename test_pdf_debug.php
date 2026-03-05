<?php
require_once __DIR__ . '/vendor/autoload.php';

use Smalot\PdfParser\Parser;

$filename = 'Analyse-Syntaxique-699f8a143c22c.pdf';
$filePath = __DIR__ . '/public/uploads/courses/' . $filename;

echo "Testing extraction for: $filePath\n";

if (!file_exists($filePath)) {
    die("File does not exist!\n");
}

try {
    $parser = new Parser();
    $pdfContent = $parser->parseFile($filePath);
    $text = $pdfContent->getText();
    
    echo "Raw text length: " . strlen($text) . "\n";
    echo "First 100 chars of raw text: " . substr($text, 0, 100) . "\n";
    
    // Test the cleaning logic
    $cleaned = cleanText($text);
    echo "Cleaned text length: " . strlen($cleaned) . "\n";
    echo "First 100 chars of cleaned text: " . substr($cleaned, 0, 100) . "\n";
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

function cleanText(string $text): string
{
    // Better regex: Keep all printable UTF-8 characters
    // \p{L} = Any letter, \p{N} = Any number, \p{P} = Any punctuation, \p{S} = Any symbol, \s = Whitespace
    // This is much safer for international text.
    $text_orig = preg_replace('/[^\p{L}\p{N}\p{P}\p{S}\s]/u', '', $text); 
    
    if ($text_orig === null) {
        echo "Regex failed (null returned)! Likely invalid UTF-8.\n";
        // Fallback: strip EVERYTHING non-ASCII without /u
        $text_orig = preg_replace('/[[:cntrl:]]/', '', $text);
    }
    
    echo "Length after better regex: " . strlen($text_orig) . "\n";
    
    $text = $text_orig;
    $text = str_replace(["\r\n", "\r"], "\n", $text);
    $text = preg_replace('/[ \t]+/', ' ', $text);
    $text = preg_replace('/\n{3,}/', "\n\n", $text);
    
    return trim($text);
}
