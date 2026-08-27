/**
 * Découpe une saisie de compétences sur les virgules et retourne des entrées uniques.
 */
export function parseSkillsInput(input: string): string[] {
    if (!input || !input.trim()) {
        return [];
    }

    return [...new Set(
        input
            .split(',')
            .map((s) => s.trim())
            .filter(Boolean)
    )];
}
