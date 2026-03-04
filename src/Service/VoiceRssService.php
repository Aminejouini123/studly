<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class VoiceRssService
{
    private const API_URL = 'https://api.voicerss.org/';

    public function __construct(
        private HttpClientInterface $httpClient,
        private string $apiKey
    ) {
    }

    public function generateSpeech(string $text, string $languageCode = 'fr-fr'): string
    {
        if (empty($this->apiKey) || $this->apiKey === 'YOUR_VOICERSS_API_KEY_HERE') {
            throw new \Exception("Clé API VoiceRSS non configurée dans le fichier .env");
        }

        $response = $this->httpClient->request('POST', self::API_URL, [
            'body' => [
                'key' => $this->apiKey,
                'src' => $text,
                'hl' => $languageCode,
                'v' => 'Bérenger', // French male voice, others: Louise, Alice...
                'r' => '-1', // speed - slightly slower for professional feel
                'c' => 'mp3',
                'f' => '44khz_16bit_stereo',
            ],
            'verify_peer' => false,
            'timeout' => 60,
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new \Exception("VoiceRSS API Error: " . $response->getStatusCode());
        }

        $content = $response->getContent();

        // VoiceRSS returns error message in text if something is wrong (quota, etc.)
        if (str_starts_with($content, 'ERROR:')) {
            throw new \Exception("VoiceRSS Service Error: " . $content);
        }

        return $content;
    }

    public function optimizeTextForSpeech(string $text, string $courseTitle): string
    {
        // 1. Add Professional Intro
        $intro = "Voici une lecture audio de votre cours intitulé : " . $courseTitle . ". ";
        $intro .= "Prenez une position confortable et laissez-vous guider. \n\n";

        // 2. Add structural pauses
        // Replace single newlines (likely sentence/list item breaks) with a comma for a short pause
        // Replace double newlines (paragraph/header breaks) with a period followed by a long elliptic pause
        $optimized = str_replace("\n\n", "... ... ", $text);
        $optimized = str_replace("\n", ", ", $optimized);

        // 3. Clean up punctuation for smoother reading
        // Ensure we don't have things like ", ." or "... ."
        $optimized = preg_replace('/[,.]\s*[.]{3}/', '...', (string) $optimized);
        $optimized = preg_replace('/[.]{3}\s*[,]/', '...', (string) $optimized);

        // 4. Add Professional Outro
        $outro = "\n\n ... Fin de la lecture. Bonne réussite dans vos études avec Studly!";

        return $intro . trim((string) $optimized) . $outro;
    }
}
