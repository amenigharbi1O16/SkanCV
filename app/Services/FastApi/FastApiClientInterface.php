<?php

namespace App\Services\FastApi;

/**
 * MISSION : contrat HTTP entre Laravel et le microservice FastAPI.
 *
 * ProcessCvAnalysis ne connaît que cette interface — jamais Fake ou Real.
 * Le binding concret est choisi dans AppServiceProvider via FASTAPI_MODE.
 */
interface FastApiClientInterface
{
    /**
     * Extrait texte et compétences d'un PDF CV stocké sur le disque local.
     *
     * @return array{
     *     text: string,
     *     skills: array<string>,
     *     candidate_name: ?string
     * }
     */
    public function extract(string $filePath): array;

    /**
     * Calcule le score de matching entre compétences CV et offre.
     *
     * @return array{
     *     score: float,
     *     justification: string,
     *     matching_skills: array<string>,
     *     missing_skills: array<string>
     * }
     */
    public function score(array $cvSkills, array $requiredSkills): array;
}
