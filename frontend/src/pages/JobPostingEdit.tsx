import { useState, useEffect, FormEvent, KeyboardEvent } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import client from '../api/client';
import { parseSkillsInput } from '../utils/skills';

interface FormData {
    title: string;
    description: string;
    required_skills: string[];
}

export default function JobPostingEdit(): JSX.Element {
    const { id } = useParams<{ id: string }>();
    const navigate = useNavigate();
    const [formData, setFormData] = useState<FormData>({
        title: '',
        description: '',
        required_skills: [],
    });
    const [currentSkill, setCurrentSkill] = useState('');
    const [loading, setLoading] = useState(true);
    const [submitting, setSubmitting] = useState(false);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        const fetchJobPosting = async (): Promise<void> => {
            try {
                const response = await client.get<{ data?: FormData } & FormData>(`/job-postings/${id}`);
                const data = response.data?.data ?? response.data;
                setFormData({
                    title: data.title || '',
                    description: data.description || '',
                    required_skills: data.required_skills || [],
                });
            } catch {
                setError("Impossible de charger l'offre d'emploi.");
            } finally {
                setLoading(false);
            }
        };
        fetchJobPosting();
    }, [id]);

    const handleAddSkill = (e: FormEvent | KeyboardEvent): void => {
        e.preventDefault();
        const newSkills = parseSkillsInput(currentSkill);
        if (newSkills.length === 0) return;
        setFormData({ ...formData, required_skills: [...new Set([...formData.required_skills, ...newSkills])] });
        setCurrentSkill('');
    };

    const handleRemoveSkill = (skillToRemove: string): void => {
        setFormData({ ...formData, required_skills: formData.required_skills.filter((s) => s !== skillToRemove) });
    };

    const handleSubmit = async (e: FormEvent<HTMLFormElement>): Promise<void> => {
        e.preventDefault();
        setSubmitting(true);
        setError(null);
        try {
            await client.put(`/job-postings/${id}`, formData);
            window.dispatchEvent(new Event('job-postings-updated'));
            navigate(`/job-postings/${id}`);
        } catch (err: unknown) {
            const axiosErr = err as { response?: { data?: { message?: string } } };
            setError(axiosErr.response?.data?.message || "Erreur lors de la modification de l'offre.");
        } finally {
            setSubmitting(false);
        }
    };

    if (loading) {
        return (
            <div className="max-w-2xl mx-auto space-y-6">
                <div className="h-10 bg-surface border border-line rounded-xl animate-pulse" />
                <div className="h-64 bg-surface border border-line rounded-xl animate-pulse" />
            </div>
        );
    }

    return (
        <div className="max-w-2xl mx-auto">
            <div className="mb-6 flex items-center gap-3">
                <Link to={`/job-postings/${id}`} className="text-muted hover:text-ink transition">
                    <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                        <path strokeLinecap="round" strokeLinejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </Link>
                <div>
                    <h1 className="font-display font-semibold text-2xl text-ink">Modifier l'offre d'emploi</h1>
                    <p className="text-sm text-muted">Ajustez le titre, la description ou les compétences cibles pour l'analyse IA.</p>
                </div>
            </div>

            {error && <div className="p-4 rounded-lg bg-danger-soft text-danger text-sm mb-6">{error}</div>}

            <form onSubmit={handleSubmit} className="bg-surface border border-line rounded-xl p-6 space-y-6 shadow-sm">
                <div>
                    <label className="block text-sm font-medium text-ink mb-1.5">Titre du poste</label>
                    <input type="text" required
                        className="w-full bg-paper border border-line rounded-lg px-3.5 py-2.5 text-sm text-ink focus:outline-none focus:ring-2 focus:ring-signal/30 focus:border-signal transition"
                        value={formData.title}
                        onChange={(e) => setFormData({ ...formData, title: e.target.value })}
                        placeholder="ex: Développeur Laravel Senior" />
                </div>

                <div>
                    <label className="block text-sm font-medium text-ink mb-1.5">Description</label>
                    <textarea required rows={5}
                        className="w-full bg-paper border border-line rounded-lg px-3.5 py-2.5 text-sm text-ink focus:outline-none focus:ring-2 focus:ring-signal/30 focus:border-signal transition"
                        value={formData.description}
                        onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                        placeholder="Détails du poste, missions..." />
                </div>

                <div>
                    <label className="block text-sm font-medium text-ink mb-1.5">Compétences requises</label>
                    <div className="flex gap-2 mb-3">
                        <input type="text"
                            className="flex-1 bg-paper border border-line rounded-lg px-3.5 py-2.5 text-sm text-ink focus:outline-none focus:ring-2 focus:ring-signal/30 focus:border-signal transition"
                            value={currentSkill}
                            onChange={(e) => setCurrentSkill(e.target.value)}
                            placeholder="ex: Docker, Python, React..."
                            onKeyDown={(e) => { if (e.key === 'Enter') { e.preventDefault(); handleAddSkill(e); } }} />
                        <button type="button" onClick={handleAddSkill}
                            className="px-4 py-2.5 bg-paper text-ink font-medium text-sm rounded-lg hover:bg-line border border-line transition">
                            Ajouter
                        </button>
                    </div>

                    {formData.required_skills.length > 0 ? (
                        <div className="flex flex-wrap gap-2 pt-1">
                            {formData.required_skills.map((skill) => (
                                <span key={skill} className="inline-flex items-center gap-1.5 px-3 py-1.5 bg-signal-soft text-signal-dark rounded-lg text-xs font-mono font-medium">
                                    {skill}
                                    <button type="button" onClick={() => handleRemoveSkill(skill)}
                                        className="text-signal hover:text-danger transition focus:outline-none"
                                        aria-label={`Supprimer ${skill}`}>
                                        <svg className="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2.5}>
                                            <path strokeLinecap="round" strokeLinejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </span>
                            ))}
                        </div>
                    ) : (
                        <p className="text-xs text-muted">Ajoutez au moins une compétence pour permettre le scoring IA.</p>
                    )}
                </div>

                <div className="flex justify-end gap-3 pt-4 border-t border-line">
                    <Link to={`/job-postings/${id}`} className="px-4 py-2.5 text-muted hover:text-ink font-medium text-sm transition">
                        Annuler
                    </Link>
                    <button type="submit" disabled={submitting}
                        className="px-5 py-2.5 bg-signal text-white font-medium text-sm rounded-lg hover:bg-signal-dark transition disabled:opacity-50">
                        {submitting ? 'Enregistrement...' : 'Enregistrer les modifications'}
                    </button>
                </div>
            </form>
        </div>
    );
}
