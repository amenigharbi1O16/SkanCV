/**
 * JobPostingCard.tsx — Carte d'une offre d'emploi sur le Dashboard.
 */
import type { JobPosting } from '../types';

interface JobPostingCardProps {
    jobPosting: JobPosting;
}

export default function JobPostingCard({ jobPosting }: JobPostingCardProps): JSX.Element {
    const skills = jobPosting.required_skills ?? [];

    return (
        <div className="bg-surface border border-line rounded-xl p-5 hover:border-signal/40 hover:shadow-sm transition group">
            <div className="flex items-start justify-between mb-3">
                <h3 className="font-display font-semibold text-ink text-base leading-snug pr-2">
                    {jobPosting.title}
                </h3>
                <span className="shrink-0 font-mono text-xs text-muted bg-paper px-2 py-1 rounded-md">
                    {jobPosting.cvs_count ?? 0} CV{(jobPosting.cvs_count ?? 0) !== 1 ? 's' : ''}
                </span>
            </div>

            {jobPosting.description && (
                <p className="text-sm text-muted mb-4 line-clamp-2">{jobPosting.description}</p>
            )}

            {skills.length > 0 && (
                <div className="flex flex-wrap gap-1.5">
                    {skills.slice(0, 5).map((skill) => (
                        <span
                            key={skill}
                            className="text-xs font-mono text-signal bg-signal-soft px-2 py-1 rounded-md"
                        >
                            {skill}
                        </span>
                    ))}
                    {skills.length > 5 && (
                        <span className="text-xs text-muted px-2 py-1">+{skills.length - 5}</span>
                    )}
                </div>
            )}
        </div>
    );
}
