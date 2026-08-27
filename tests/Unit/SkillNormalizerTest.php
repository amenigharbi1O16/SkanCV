<?php

namespace Tests\Unit;

use App\Support\SkillNormalizer;
use PHPUnit\Framework\TestCase;

class SkillNormalizerTest extends TestCase
{
    public function test_splits_comma_separated_skills(): void
    {
        $result = SkillNormalizer::normalize([
            'React, Node.js, Laravel, JavaScript, Tailwind CSS, REST API',
        ]);

        $this->assertSame(
            ['React', 'Node.js', 'Laravel', 'JavaScript', 'Tailwind CSS', 'REST API'],
            $result
        );
    }

    public function test_deduplicates_case_insensitive(): void
    {
        $result = SkillNormalizer::normalize(['PHP', 'php', 'Laravel']);

        $this->assertCount(2, $result);
    }

    public function test_splits_space_separated_lowercase_skills(): void
    {
        $result = SkillNormalizer::normalize(['react django php python']);

        $this->assertSame(['react', 'django', 'php', 'python'], $result);
    }
}
