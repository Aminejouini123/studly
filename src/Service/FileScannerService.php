<?php

namespace App\Service;

use Smalot\PdfParser\Parser;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class FileScannerService // Service pour extraire le texte de différents types de fichiers (PDF, Word, etc.)
{ // Début de la classe
    private string $uploadsDir; // Variable pour stocker le chemin du dossier des fichiers

    public function __construct( // Constructeur pour initialiser le service
        #[Autowire('%kernel.project_dir%/public/uploads')] string $uploadsDir // Injection automatique du chemin des uploads de base
    ) { // Début du constructeur
        $this->uploadsDir = $uploadsDir; // Assignation du chemin injecté à la variable de classe
    } // Fin du constructeur

    /**
     * Extracts text content from a file based on its extension.
     */ // Commentaire de documentation
    public function extractText(string $filename, string $subDir = 'courses'): string // Fonction principale pour extraire le texte d'un fichier
    { // Début de la fonction
        $filePath = $this->uploadsDir . DIRECTORY_SEPARATOR . $subDir . DIRECTORY_SEPARATOR . $filename; // Construction du chemin absolu complet vers le fichier

        if (!file_exists($filePath)) { // Vérification si le fichier existe physiquement sur le serveur
            return ""; // Si le fichier est absent, on retourne une chaîne vide
        } // Fin du test d'existence

        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION)); // Récupération de l'extension (ex: pdf, docx, txt)

        switch ($extension) { // Choix de la méthode d'extraction selon le type de fichier
            case 'pdf': // Cas d'un fichier PDF
                return $this->extractFromPdf($filePath); // Appel de la fonction pour extraire le texte d'un PDF
            case 'docx': // Cas d'un fichier Word
                return $this->extractFromDocx($filePath); // Appel de la fonction pour extraire le texte d'un DOCX
            case 'pptx': // Cas d'un fichier PowerPoint
                return $this->extractFromPptx($filePath); // Appel de la fonction pour extraire le texte d'un PPTX
            case 'txt': // Cas de fichiers texte simple
            case 'md': // Fichier Markdown
            case 'csv': // Fichier CSV
            case 'json': // Fichier JSON
                return $this->extractFromPlainText($filePath); // Appel de la fonction pour lire le texte brut
            default: // Pour tous les autres formats non supportés
                // For unknown types, we'll try plain text if it's small, or return file info
                return "File: $filename (Format: $extension). Content extraction not supported for this format."; // Message d'information
        } // Fin du switch
    } // Fin de la fonction extractText

    private function extractFromPdf(string $filePath): string // Fonction privée pour les PDF
    { // Début de la fonction
        try { // Début du bloc de sécurité
            $parser = new Parser(); // Création du parseur PDF (Smalot)
            $pdfContent = $parser->parseFile($filePath); // Analyse du fichier PDF
            $text = $pdfContent->getText(); // Récupération du texte extrait
            return $this->cleanText($text); // Retourne le texte après nettoyage
        } catch (\Exception $e) { // En cas d'erreur de lecture
            return ""; // Retourne vide
        } // Fin du try-catch
    } // Fin de la fonction PDF

    private function extractFromDocx(string $filePath): string // Fonction pour les fichiers Word
    { // Début de la fonction
        return $this->extractFromZipXml($filePath, 'word/document.xml'); // Les fichiers DOCX stockent le texte dans document.xml
    } // Fin de la fonction DOCX

    private function extractFromPptx(string $filePath): string // Fonction pour les fichiers PowerPoint
    { // Début de la fonction
        // PowerPoint has multiple slide files, this is a simplified version
        $zip = new \ZipArchive(); // Utilisation de ZipArchive car PPTX est un fichier compressé
        $fullText = ""; // Initialisation de la variable de texte
        if ($zip->open($filePath) === true) { // Si l'archive s'ouvre correctement
            for ($i = 0; $i < $zip->numFiles; $i++) { // On parcourt tous les fichiers à l'intérieur du ZIP
                $stat = $zip->statIndex($i); // Récupération des infos du fichier actuel
                if (preg_match('/ppt\/slides\/slide\d+\.xml/', $stat['name'])) { // Si c'est un fichier XML de diapositive
                    $xml = $zip->getFromName($stat['name']); // On lit le contenu XML de la slide
                    $fullText .= strip_tags($xml) . " "; // On enlève les balises XML pour garder le texte
                } // Fin de la condition slide
            } // Fin de la boucle
            $zip->close(); // Fermeture de l'archive ZIP
        } // Fin du test d'ouverture
        return $this->cleanText($fullText); // Retourne tout le texte des slides nettoyé
    } // Fin de la fonction PPTX

    private function extractFromZipXml(string $filePath, string $xmlPath): string // Fonction utilitaire pour lire du XML dans un ZIP
    { // Début de la fonction
        $zip = new \ZipArchive(); // Création de l'objet ZIP
        $content = ""; // Initialisation du contenu
        if ($zip->open($filePath) === true) { // Si ouverture réussie
            if (($index = $zip->locateName($xmlPath)) !== false) { // Si le fichier XML cible est trouvé
                $xml = $zip->getFromIndex($index); // Lecture du XML
                $content = strip_tags($xml); // Extraction du texte brut
            } // Fin du test XML
            $zip->close(); // Fermeture ZIP
        } // Fin du test ZIP
        return $this->cleanText($content); // Retour après nettoyage
    } // Fin de la fonction utilitaire

    private function extractFromPlainText(string $filePath): string // Fonction pour les fichiers texte
    { // Début de la fonction
        try { // Bloc de sécurité
            $content = file_get_contents($filePath); // Lecture directe du contenu du fichier
            return $this->cleanText($content); // Retour après nettoyage
        } catch (\Exception $e) { // En cas d'erreur
            return ""; // Retourne vide
        } // Fin du try-catch
    } // Fin de la fonction texte brut

    private function cleanText(string $text): string // Fonction pour nettoyer le texte extrait
    { // Début de la fonction
        // Remove non-printable characters except common whitespace (newlines, tabs)
        $text = preg_replace('/[^\x20-\x7E\s\r\n\t]/u', '', $text); 
        
        // Normalize line endings
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        
        // Collapse multiple spaces but keep newlines
        $text = preg_replace('/[ \t]+/', ' ', $text);
        
        // Collapse multiple newlines into max two (single empty line between blocks)
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        
        return trim($text); // Suppression des espaces inutiles au début et à la fin
    } // Fin de la fonction de nettoyage
}
