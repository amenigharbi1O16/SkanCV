import { useState, FormEvent, KeyboardEvent } from 'react';
import { useNavigate } from 'react-router-dom';
import client from '../api/client';
import { parseSkillsInput } from '../utils/skills';

interface FormData {
    title: string;
    description: string;
    required_skills: string[];
}

export default function JobPostingCreate(): JSX.Element {
    const navigate = useNavigate();
    const [formData, setFormData] = useState<FormData>({
        title: '',
        description: '',
        required_skills: [],
    });
    const [currentSkill, setCurrentSkill] = useState('');
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);

    const handleAddSkill = (e: FormEvent | KeyboardEvent): void => {
        e.preventDefault();
        const newSkills = parseSkillsInput(currentSkill);
        if (newSkills.length === 0) return;
        setFormData({
            ...formData,
            required_skills: [...new Set([...formData.required_skills, ...newSkills])],
        });
        setCurrentSkill('');
    };

    const handleRemoveSkill = (skillToRemove: string): void => {
        setFormData({
            ...formData,
            required_skills: formData.required_skills.filter((skill) => skill !== skillToRemove),
        });
    };

    const handleSubmit = async (e: FormEvent<HTMLFormElement>): Promise<void> => {
        e.preventDefault();
        setLoading(true);
        setError(null);
        try {
            const response = await client.post<{ id?: number; data?: { id?: number } }>('/job-postings', formData);
            const newId = response.data?.id ?? response.data?.data?.id;
            window.dispatchEvent(new Event('job-postings-updated'));
            navigate(newId ? `/job-postings/${newId}` : '/');
        } catch (err: unknown) {
            const axiosErr = err as { response?: { data?: { message?: string } } };
            setError(axiosErr.response?.data?.message || "Erreur lors de la création de l'offre.");
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="max-w-2xl mx-auto">
            <div className="mb-8 flex items-center justify-between">
                <div>
                    <h1 className="font-display font-semibold text-2xl text-ink">Nouvelle offre d'emploi</h1>
                    <p className="text-sm text-muted mt-1">Créez une offre pour commencer à analyser les CVs.</p>
                </div>
            </div>

            {error && <div className="p-4 rounded-lg bg-danger-soft text-danger text-sm mb-6">{error}</div>}

            <form onSubmit={handleSubmit} className="bg-surface border border-line rounded-xl p-6 space-y-6 shadow-sm">
                <div>
                    <label className="block text-sm font-medium text-ink mb-1">Titre du poste</label>
                    <input
                        type="text" required
                        className="w-full border border-line rounded-lg px-3 py-2 focus-visible:outline focus-visible:outline-2 focus-visible:outline-signal"
                        value={formData.title}
                        onChange={(e) => setFormData({ ...formData, title: e.target.value })}
                        placeholder="ex: Développeur Laravel Senior"
                    />
                </div>

                <div>
                    <label className="block text-sm font-medium text-ink mb-1">Description</label>
                    <textarea
                        required rows={4}
                        className="w-full border border-line rounded-lg px-3 py-2 focus-visible:outline focus-visible:outline-2 focus-visible:outline-signal"
                        value={formData.description}
                        onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                        placeholder="Détails du poste, missions..."
                    />
                </div>

                <div>
                    <label className="block text-sm font-medium text-ink mb-1">Compétences requises</label>
                    <div className="flex gap-2 mb-3">
                        <input
                            type="text"
                            className="flex-1 border border-line rounded-lg px-3 py-2 focus-visible:outline focus-visible:outline-2 focus-visible:outline-signal"
                            value={currentSkill}
                            onChange={(e) => setCurrentSkill(e.target.value)}
                            placeholder="ex: React, Laravel, PHP"
                            onKeyDown={(e) => { if (e.key === 'Enter') { e.preventDefault(); handleAddSkill(e); } }}
                        />
                        <button type="button" onClick={handleAddSkill} className="px-4 py-2 bg-paper text-ink font-medium rounded-lg hover:bg-line border border-line transition">
                            Ajouter
                        </button>
                    </div>

                    {formData.required_skills.length > 0 && (
                        <div className="flex flex-wrap gap-2">
                            {formData.required_skills.map((skill) => (
                                <span key={skill} className="inline-flex items-center gap-1.5 px-3 py-1 bg-signal-soft text-signal-dark rounded-full text-sm font-medium">
                                    {skill}
                                    <button type="button" onClick={() => handleRemoveSkill(skill)} className="text-signal hover:text-signal-dark">&times;</button>
                                </span>
                            ))}
                        </div>
                    )}
                </div>

                <div className="pt-4 flex justify-end gap-3 border-t border-line">
                    <button type="button" onClick={() => navigate('/')} className="px-4 py-2 text-muted hover:text-ink font-medium transition">
                        Annuler
                    </button>
                    <button type="submit" disabled={loading || formData.required_skills.length === 0}
                        className="px-5 py-2 bg-signal text-white font-medium rounded-lg hover:bg-signal-dark transition disabled:opacity-50">
                        {loading ? 'Création...' : "Créer l'offre"}
                    </button>
                </div>
            </form>
        </div>
    );
}
