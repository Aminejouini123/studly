<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

final class OpenRouterClient // Classe finale pour communiquer avec l'API OpenRouter
{ // Début de la classe
    public function __construct( // Constructeur pour configurer la connexion API
        private HttpClientInterface $http, // Client HTTP de Symfony pour envoyer les requêtes
        private string $apiKey, // Votre clé API secrète
        private string $model, // Le modèle d'IA à utiliser
        private string $appUrl, // URL de votre application (requis par OpenRouter)
        private string $appTitle, // Titre de votre application (requis par OpenRouter)
    ) {
    } // Fin du constructeur

    /**
     * @param array<int, array{role:string, content:string}> $messages
     * @return array<string, mixed>
     */ // Documentation PHPStan pour les messages
    public function chat(array $messages): array // Fonction pour envoyer un message à l'IA
    { // Début de la fonction
        $response = $this->http->request('POST', 'https://openrouter.ai/api/v1/chat/completions', [ // Envoi d'une requête POST à OpenRouter
            'headers' => [ // Définition des entêtes HTTP de la requête
                'Authorization' => 'Bearer ' . $this->apiKey, // Authentification avec la clé API
                'Content-Type' => 'application/json', // Indication du format JSON
                // Optional but recommended by OpenRouter:
                'HTTP-Referer' => $this->appUrl, // URL source de la requête
                'X-Title' => $this->appTitle, // Nom de l'application source
            ], // Fin des entêtes
            'json' => [ // Corps de la requête au format JSON
                'model' => $this->model, // Modèle d'IA cible
                'messages' => $messages, // Liste des messages échangés
            ], // Fin du contenu JSON
            'verify_peer' => false,
            'timeout' => 30, // Temps maximum d'attente (30 secondes)
        ]); // Fin de la configuration de la requête

        return $response->toArray(false); // Conversion de la réponse JSON en tableau PHP et retour (false pour ignorer les erreurs HTTP)
    } // Fin de la fonction chat
} // Fin de la classe
