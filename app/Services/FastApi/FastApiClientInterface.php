<?php

namespace App\Services\FastApi;

interface FastApiClientInterface
{
    /**
     * @return array{
     *     text: string,
     *     skills: array<string>,
     *     candidate_name: ?string
     * }
     */
    public function extract(string $filePath): array;

    /**
     * @return array{
     *     score: float,
     *     justification: string,
     *     matching_skills: array<string>,
     *     missing_skills: array<string>
     * }
     */
    public function score(array $cvSkills, array $requiredSkills): array;
}
