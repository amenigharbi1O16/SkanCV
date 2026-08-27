<?php

namespace App\Services\FastApi;

/**
 * MISSION : simuler FastAPI en dev/test sans microservice Python.
 *
 * Activé quand FASTAPI_MODE=fake. Extraction basée sur le nom du fichier ;
 * scoring avec intersection insensible à la casse.
 */
class FakeFastApiClient implements FastApiClientInterface
{
    private const DEFAULT_SKILLS = [
        'PHP', 'Laravel', 'JavaScript', 'React', 'Node.js', 'MySQL', 'Docker', 'Git',
    ];

    public function extract(string $filePath): array
    {
        $basename = pathinfo($filePath, PATHINFO_FILENAME);
        $nameGuess = str_replace(['_', '-'], ' ', $basename);

        return [
            'text' => "Candidat {$nameGuess}, développeur avec expérience PHP, Laravel, React et Docker.",
            'skills' => self::DEFAULT_SKILLS,
            'candidate_name' => ucwords($nameGuess),
        ];
    }

    public function score(array $cvSkills, array $requiredSkills): array
    {
        $requiredSkills = $this->normalizeList($requiredSkills);
        $cvSkills = $this->normalizeList($cvSkills);

        if ($requiredSkills === []) {
            return [
                'score' => 0.0,
                'justification' => 'Aucune compétence requise définie sur l\'offre.',
                'matching_skills' => [],
                'missing_skills' => [],
            ];
        }

        $cvLower = array_map('mb_strtolower', $cvSkills);
        $matching = [];
        foreach ($requiredSkills as $required) {
            if (in_array(mb_strtolower($required), $cvLower, true)) {
                $matching[] = $required;
            }
        }

        $matching = array_values(array_unique($matching));
        $missing = array_values(array_diff($requiredSkills, $matching));

        $score = round(count($matching) / count($requiredSkills), 4);

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

    /** @param  list<string>  $skills */
    private function normalizeList(array $skills): array
    {
        $flat = [];
        foreach ($skills as $skill) {
            foreach (preg_split('/\s*,\s*/', (string) $skill) ?: [] as $part) {
                $clean = trim($part);
                if ($clean !== '') {
                    $flat[] = $clean;
                }
            }
        }

        return array_values(array_unique($flat));
    }
}
