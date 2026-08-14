<?php

namespace App\Services\FastApi;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * MISSION : appeler le microservice FastAPI réel via HTTP.
 *
 * Activé quand FASTAPI_MODE=real. Envoie le PDF en multipart à /extract
 * et les skills en JSON à /score. Les timeouts évitent de bloquer le worker.
 */
class RealFastApiClient implements FastApiClientInterface
{
    public function __construct(private readonly string $baseUrl)
    {
    }

    /**
     * POST /extract — envoie le PDF binaire (aligné avec UploadFile côté FastAPI).
     */
    public function extract(string $filePath): array
    {
        $fullPath = Storage::disk('local')->path($filePath);

        if (! is_readable($fullPath)) {
            throw new RuntimeException("Fichier CV introuvable : {$filePath}");
        }

        try {
            $response = Http::timeout(120)
                ->attach('file', file_get_contents($fullPath), basename($fullPath))
                ->post(rtrim($this->baseUrl, '/').'/extract');
        } catch (RequestException $e) {
            throw new RuntimeException('Échec appel FastAPI /extract : '.$e->getMessage(), 0, $e);
        }

        if ($response->failed()) {
            throw new RuntimeException('FastAPI /extract a répondu '.$response->status());
        }

        return $response->json();
    }

    /**
     * POST /score — envoie cv_skills et required_skills en JSON.
     */
    public function score(array $cvSkills, array $requiredSkills): array
    {
        try {
            $response = Http::timeout(60)
                ->post(rtrim($this->baseUrl, '/').'/score', [
                    'cv_skills' => $cvSkills,
                    'required_skills' => $requiredSkills,
                ]);
        } catch (RequestException $e) {
            throw new RuntimeException('Échec appel FastAPI /score : '.$e->getMessage(), 0, $e);
        }

        if ($response->failed()) {
            throw new RuntimeException('FastAPI /score a répondu '.$response->status());
        }

        return $response->json();
    }
}
