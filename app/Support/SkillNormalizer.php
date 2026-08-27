<?php

namespace App\Support;

/**
 * Normalise une liste de compétences : split sur les virgules, trim, déduplication.
 *
 * Corrige le cas où le HR colle "React, Laravel, PHP" comme une seule entrée
 * au lieu de trois skills séparées — sinon le score de matching reste à 0.
 */
class SkillNormalizer
{
    public static function normalize(array $skills): array
    {
        $normalized = [];

        foreach ($skills as $skill) {
            if (! is_string($skill)) {
                continue;
            }

            foreach (self::splitSkill($skill) as $part) {
                $clean = trim($part);
                if ($clean === '') {
                    continue;
                }

                $key = mb_strtolower($clean);
                if (! isset($normalized[$key])) {
                    $normalized[$key] = $clean;
                }
            }
        }

        return array_values($normalized);
    }

    /**
     * @return list<string>
     */
    private static function splitSkill(string $skill): array
    {
        $skill = trim($skill);
        if ($skill === '') {
            return [];
        }

        if (str_contains($skill, ',')) {
            return preg_split('/\s*,\s*/', $skill) ?: [];
        }

        // "react django php" collé en une seule entrée (sans virgule)
        if (preg_match('/^[a-z0-9\s]+$/', $skill) && str_contains($skill, ' ')) {
            return preg_split('/\s+/', $skill) ?: [];
        }

        return [$skill];
    }
}
