<?php

namespace App\Services\FastApi;

/**
 * MISSION : simuler FastAPI en dev/test sans microservice Python.
 *
 * Activé quand FASTAPI_MODE=fake. Retourne des données réalistes avec
 * une vraie intersection de skills pour valider le pipeline Laravel.
 */
class FakeFastApiClient implements FastApiClientInterface
{
    /** Simule POST /extract — pas de lecture PDF réelle. */
    public function extract(string $filePath): array
    {
        return [
            'text' => 'Jean Dupont, développeur avec 5 ans d\'expérience en PHP et Laravel.',
            'skills' => ['PHP', 'Laravel', 'MySQL', 'Docker'],
            'candidate_name' => 'Jean Dupont',
        ];
    }

    /** Simule POST /score — intersection simple (sans embeddings). */
    public function score(array $cvSkills, array $requiredSkills): array
    {
        $matching = array_values(array_intersect($cvSkills, $requiredSkills));
        $missing = array_values(array_diff($requiredSkills, $cvSkills));

        $score = count($requiredSkills) > 0
            ? round(count($matching) / count($requiredSkills), 4)
            : 0.0;

        return [
            'score' => $score,
            'justification' => sprintf(
                'Le candidat maîtrise %d compétence(s) sur %d requises : %s. Compétences manquantes : %s.',
                count($matching),
                count($requiredSkills),
                implode(', ', $matching) ?: 'aucune',
                implode(', ', $missing) ?: 'aucune'
            ),
            'matching_skills' => $matching,
            'missing_skills' => $missing,
        ];
    }
}
