<?php

namespace App\Services\FastApi;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * MISSION : appeler le microservice FastAPI réel via HTTP.
 *
 * Activé quand FASTAPI_MODE=real. Envoie le PDF en multipart à /extract
 * et les skills en JSON à /score.
 */
class RealFastApiClient implements FastApiClientInterface
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly ?string $apiKey = null,
    ) {
    }

    /**
     * POST /extract — envoie le PDF binaire (aligné avec UploadFile côté FastAPI).
     */
    public function extract(string $filePath): array
    {
        if (! Storage::disk('local')->exists($filePath)) {
            throw new RuntimeException("Fichier CV introuvable : {$filePath}");
        }

        $content = Storage::disk('local')->get($filePath);

        try {
            $response = $this->client(120)
                ->attach('file', $content, basename($filePath))
                ->post(rtrim($this->baseUrl, '/').'/extract')
                ->throw();
        } catch (ConnectionException $e) {
            throw new RuntimeException('Connexion FastAPI /extract impossible : '.$e->getMessage(), 0, $e);
        }

        return $response->json();
    }

    /**
     * POST /score — envoie cv_skills et required_skills en JSON.
     */
    public function score(array $cvSkills, array $requiredSkills): array
    {
        try {
            $response = $this->client(60)
                ->post(rtrim($this->baseUrl, '/').'/score', [
                    'cv_skills' => $cvSkills,
                    'required_skills' => $requiredSkills,
                ])
                ->throw();
        } catch (ConnectionException $e) {
            throw new RuntimeException('Connexion FastAPI /score impossible : '.$e->getMessage(), 0, $e);
        }

        return $response->json();
    }

    private function client(int $timeout): PendingRequest
    {
        $request = Http::timeout($timeout);

        if ($this->apiKey) {
            $request = $request->withHeaders(['X-API-Key' => $this->apiKey]);
        }

        return $request;
    }
}
