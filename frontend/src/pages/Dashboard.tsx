import { useEffect, useState, useMemo } from 'react';
import { Link } from 'react-router-dom';
import client from '../api/client';
import JobPostingCard from '../components/JobPostingCard';
import type { JobPosting } from '../types';

export default function Dashboard(): JSX.Element {
    const [jobPostings, setJobPostings] = useState<JobPosting[] | null>(null);
    const [error, setError] = useState<string | null>(null);
    const [searchQuery, setSearchQuery] = useState('');
    const [selectedSkill, setSelectedSkill] = useState('');

    useEffect(() => {
        client.get<JobPosting[] | { data: JobPosting[] }>('/job-postings')
            .then((res) => {
                const d = res.data as { data?: JobPosting[] } & JobPosting[];
                setJobPostings(Array.isArray(res.data) ? res.data : (d.data ?? []));
            })
            .catch(() => setError("Impossible de charger les offres d'emploi."));
    }, []);

    const allSkills = useMemo((): string[] => {
        if (!jobPostings) return [];
        const set = new Set<string>();
        jobPostings.forEach((jp) => (jp.required_skills ?? []).forEach((s) => set.add(s)));
        return [...set].sort();
    }, [jobPostings]);

    const filteredJobPostings = useMemo((): JobPosting[] => {
        if (!jobPostings) return [];
        const query = searchQuery.toLowerCase().trim();
        return jobPostings.filter((jp) => {
            const matchesQuery =
                !query ||
                jp.title.toLowerCase().includes(query) ||
                jp.description?.toLowerCase().includes(query) ||
                (jp.required_skills || []).some((s) => s.toLowerCase().includes(query));
            const matchesSkill = !selectedSkill || (jp.required_skills || []).includes(selectedSkill);
            return matchesQuery && matchesSkill;
        });
    }, [jobPostings, searchQuery, selectedSkill]);

    return (
        <div className="space-y-6">
            <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 className="font-display font-semibold text-2xl text-ink">Offres d'emploi</h1>
                    <p className="text-sm text-muted mt-1">
                        Suivez vos offres actives, filtrez par compétences et analysez les candidatures.
                    </p>
                </div>
                <Link
                    to="/job-postings/create"
                    className="inline-flex items-center gap-2 px-4 py-2.5 bg-signal text-white font-medium text-sm rounded-lg hover:bg-signal-dark transition shrink-0 self-start sm:self-auto"
                >
                    <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                        <path strokeLinecap="round" strokeLinejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Nouvelle offre
                </Link>
            </div>

            {error && <div className="p-4 rounded-lg bg-danger-soft text-danger text-sm">{error}</div>}

            {jobPostings && jobPostings.length > 0 && (
                <div className="bg-surface border border-line rounded-xl p-4 shadow-sm space-y-3">
                    <div className="relative">
                        <svg className="w-4 h-4 text-muted absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                            <path strokeLinecap="round" strokeLinejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input
                            type="text"
                            value={searchQuery}
                            onChange={(e) => setSearchQuery(e.target.value)}
                            placeholder="Rechercher par mot-clé, titre de poste ou compétence..."
                            className="w-full pl-10 pr-4 py-2 bg-paper border border-line rounded-lg text-sm text-ink focus:outline-none focus:ring-2 focus:ring-signal/30 focus:border-signal transition"
                        />
                        {searchQuery && (
                            <button onClick={() => setSearchQuery('')} className="absolute right-3 top-1/2 -translate-y-1/2 text-muted hover:text-ink text-xs font-semibold">
                                Effacer
                            </button>
                        )}
                    </div>

                    {allSkills.length > 0 && (
                        <div className="flex flex-wrap items-center gap-1.5 pt-1">
                            <span className="text-xs text-muted font-medium mr-1">Filtres rapides :</span>
                            <button
                                onClick={() => setSelectedSkill('')}
                                className={`px-2.5 py-1 rounded-md text-xs font-medium transition ${!selectedSkill ? 'bg-ink text-white' : 'bg-paper text-muted hover:text-ink border border-line'}`}
                            >
                                Tous
                            </button>
                            {allSkills.map((skill) => (
                                <button
                                    key={skill}
                                    onClick={() => setSelectedSkill(selectedSkill === skill ? '' : skill)}
                                    className={`px-2.5 py-1 rounded-md text-xs font-medium transition ${selectedSkill === skill ? 'bg-signal text-white' : 'bg-paper text-muted hover:text-ink border border-line'}`}
                                >
                                    {skill}
                                </button>
                            ))}
                        </div>
                    )}
                </div>
            )}

            {jobPostings === null && !error && (
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    {[1, 2, 3, 4, 5, 6].map((i) => (
                        <div key={i} className="h-40 rounded-xl bg-surface border border-line animate-pulse" />
                    ))}
                </div>
            )}

            {jobPostings !== null && jobPostings.length === 0 && (
                <div className="border border-dashed border-line rounded-xl p-12 text-center bg-surface">
                    <p className="text-ink font-medium mb-1">Aucune offre pour le moment</p>
                    <p className="text-sm text-muted mb-4">Créez votre première offre d'emploi pour commencer à scanner et analyser des CVs avec l'IA.</p>
                    <Link to="/job-postings/create" className="inline-flex items-center gap-2 px-4 py-2 bg-signal text-white font-medium text-sm rounded-lg hover:bg-signal-dark transition">
                        Créer une offre
                    </Link>
                </div>
            )}

            {jobPostings && jobPostings.length > 0 && filteredJobPostings.length === 0 && (
                <div className="border border-dashed border-line rounded-xl p-12 text-center bg-surface">
                    <p className="text-ink font-medium mb-1">Aucun résultat trouvé</p>
                    <p className="text-sm text-muted mb-3">Aucune offre ne correspond à vos critères de recherche.</p>
                    <button onClick={() => { setSearchQuery(''); setSelectedSkill(''); }} className="text-xs text-signal font-medium hover:underline">
                        Réinitialiser les filtres
                    </button>
                </div>
            )}

            {jobPostings && filteredJobPostings.length > 0 && (
                <div>
                    <div className="text-xs font-semibold text-muted uppercase tracking-wider mb-3">
                        {filteredJobPostings.length} offre{filteredJobPostings.length > 1 ? 's' : ''} disponible{filteredJobPostings.length > 1 ? 's' : ''}
                    </div>
                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        {filteredJobPostings.map((jp) => (
                            <Link key={jp.id} to={`/job-postings/${jp.id}`} className="block">
                                <JobPostingCard jobPosting={jp} />
                            </Link>
                        ))}
                    </div>
                </div>
            )}
        </div>
    );
}
