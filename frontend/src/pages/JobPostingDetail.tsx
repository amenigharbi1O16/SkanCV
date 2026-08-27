import { useEffect, useState, useMemo } from 'react';
import { useParams, Link, useNavigate } from 'react-router-dom';
import client from '../api/client';
import StatusBadge from '../components/StatusBadge';
import UploadCvModal from '../components/UploadCvModal';
import type { JobPosting, Cv } from '../types';

interface ConfirmDeleteCvModalProps {
    cv: Cv | null;
    onConfirm: () => void;
    onCancel: () => void;
}

function ConfirmDeleteCvModal({ cv, onConfirm, onCancel }: ConfirmDeleteCvModalProps): JSX.Element | null {
    if (!cv) return null;
    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-ink/50 backdrop-blur-sm">
            <div className="bg-surface rounded-xl shadow-xl w-full max-w-sm p-6 animate-tag-reveal">
                <div className="flex items-start gap-4 mb-5">
                    <div className="shrink-0 w-10 h-10 rounded-full bg-danger-soft flex items-center justify-center">
                        <svg className="w-5 h-5 text-danger" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                            <path strokeLinecap="round" strokeLinejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </div>
                    <div>
                        <h3 className="font-display font-semibold text-ink">Supprimer ce CV ?</h3>
                        <p className="text-sm text-muted mt-1">
                            Le CV de <span className="font-medium text-ink">{cv.candidate_name}</span> sera définitivement supprimé. Cette action est irréversible.
                        </p>
                    </div>
                </div>
                <div className="flex justify-end gap-3">
                    <button onClick={onCancel} className="px-4 py-2 text-muted hover:text-ink font-medium text-sm transition">Annuler</button>
                    <button onClick={onConfirm} className="px-4 py-2 bg-danger text-white font-medium text-sm rounded-lg hover:bg-red-700 transition">
                        Supprimer définitivement
                    </button>
                </div>
            </div>
        </div>
    );
}

interface ConfirmDeleteJobModalProps {
    jobTitle: string;
    isOpen: boolean;
    onConfirm: () => void;
    onCancel: () => void;
    loading: boolean;
}

function ConfirmDeleteJobModal({ jobTitle, isOpen, onConfirm, onCancel, loading }: ConfirmDeleteJobModalProps): JSX.Element | null {
    if (!isOpen) return null;
    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-ink/50 backdrop-blur-sm">
            <div className="bg-surface rounded-xl shadow-xl w-full max-w-md p-6 animate-tag-reveal">
                <div className="flex items-start gap-4 mb-5">
                    <div className="shrink-0 w-10 h-10 rounded-full bg-danger-soft flex items-center justify-center">
                        <svg className="w-5 h-5 text-danger" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                            <path strokeLinecap="round" strokeLinejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <h3 className="font-display font-semibold text-lg text-ink">Supprimer cette offre d'emploi ?</h3>
                        <p className="text-sm text-muted mt-1.5 leading-relaxed">
                            Êtes-vous sûr de vouloir supprimer l'offre <span className="font-semibold text-ink">"{jobTitle}"</span> ?
                            Tous les CVs et analyses associés seront également supprimés.
                        </p>
                    </div>
                </div>
                <div className="flex justify-end gap-3">
                    <button type="button" disabled={loading} onClick={onCancel} className="px-4 py-2 text-muted hover:text-ink font-medium text-sm transition">Annuler</button>
                    <button type="button" disabled={loading} onClick={onConfirm}
                        className="px-4 py-2 bg-danger text-white font-medium text-sm rounded-lg hover:bg-red-700 transition disabled:opacity-60 flex items-center gap-2">
                        {loading && <svg className="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"/><path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>}
                        Supprimer définitivement
                    </button>
                </div>
            </div>
        </div>
    );
}

type SortKey = 'candidate_name' | 'analysis_status' | 'created_at';
type SortDir = 'asc' | 'desc';

export default function JobPostingDetail(): JSX.Element {
    const { id } = useParams<{ id: string }>();
    const navigate = useNavigate();
    const [jobPosting, setJobPosting] = useState<JobPosting | null>(null);
    const [cvs, setCvs] = useState<Cv[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [showUpload, setShowUpload] = useState(false);
    const [cvToDelete, setCvToDelete] = useState<Cv | null>(null);
    const [showDeleteJob, setShowDeleteJob] = useState(false);
    const [deletingJob, setDeletingJob] = useState(false);
    const [cvSearch, setCvSearch] = useState('');
    const [sortKey, setSortKey] = useState<SortKey>('created_at');
    const [sortDir, setSortDir] = useState<SortDir>('desc');

    const fetchData = async (): Promise<void> => {
        try {
            const [jpRes, cvsRes] = await Promise.all([
                client.get<{ data?: JobPosting } | JobPosting>(`/job-postings/${id}`),
                client.get<{ data?: Cv[] } | Cv[]>(`/job-postings/${id}/cvs`),
            ]);
            const jpData = jpRes.data as { data?: JobPosting } & JobPosting;
            setJobPosting(jpData.data ?? jpData);
            const cvsData = cvsRes.data as { data?: Cv[] } & Cv[];
            setCvs(Array.isArray(cvsRes.data) ? cvsRes.data : (cvsData.data ?? []));
        } catch {
            setError("Impossible de charger les données de l'offre.");
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => { fetchData(); }, [id]);

    const handleDeleteCv = async (): Promise<void> => {
        if (!cvToDelete) return;
        try {
            await client.delete(`/job-postings/${id}/cvs/${cvToDelete.id}`);
            setCvs((prev) => prev.filter((c) => c.id !== cvToDelete.id));
            setCvToDelete(null);
        } catch {
            setError('Erreur lors de la suppression du CV.');
            setCvToDelete(null);
        }
    };

    const handleDeleteJob = async (): Promise<void> => {
        setDeletingJob(true);
        try {
            await client.delete(`/job-postings/${id}`);
            window.dispatchEvent(new Event('job-postings-updated'));
            navigate('/');
        } catch {
            setError("Erreur lors de la suppression de l'offre.");
            setDeletingJob(false);
            setShowDeleteJob(false);
        }
    };

    const handleSort = (key: SortKey): void => {
        if (sortKey === key) {
            setSortDir((d) => d === 'asc' ? 'desc' : 'asc');
        } else {
            setSortKey(key);
            setSortDir('asc');
        }
    };

    const filteredAndSortedCvs = useMemo((): Cv[] => {
        const query = cvSearch.toLowerCase().trim();
        const filtered = query
            ? cvs.filter((c) => c.candidate_name?.toLowerCase().includes(query) || c.file_name?.toLowerCase().includes(query))
            : cvs;

        return [...filtered].sort((a, b) => {
            let aVal: string = String(a[sortKey] ?? '');
            let bVal: string = String(b[sortKey] ?? '');
            const cmp = aVal.localeCompare(bVal);
            return sortDir === 'asc' ? cmp : -cmp;
        });
    }, [cvs, cvSearch, sortKey, sortDir]);

    if (loading) {
        return (
            <div className="space-y-6">
                <div className="h-20 bg-surface border border-line rounded-xl animate-pulse" />
                <div className="h-64 bg-surface border border-line rounded-xl animate-pulse" />
            </div>
        );
    }

    if (error && !jobPosting) {
        return <div className="p-4 rounded-lg bg-danger-soft text-danger text-sm">{error}</div>;
    }

    return (
        <div className="space-y-6">
            <ConfirmDeleteCvModal cv={cvToDelete} onConfirm={handleDeleteCv} onCancel={() => setCvToDelete(null)} />
            <ConfirmDeleteJobModal
                jobTitle={jobPosting?.title ?? ''}
                isOpen={showDeleteJob}
                onConfirm={handleDeleteJob}
                onCancel={() => setShowDeleteJob(false)}
                loading={deletingJob}
            />

            {showUpload && jobPosting && (
                <UploadCvModal
                    jobPostingId={jobPosting.id}
                    onClose={() => setShowUpload(false)}
                    onSuccess={fetchData}
                />
            )}

            {/* Header */}
            <div className="flex items-start justify-between gap-4">
                <div className="flex items-center gap-3">
                    <Link to="/" className="text-muted hover:text-ink transition">
                        <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                            <path strokeLinecap="round" strokeLinejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </Link>
                    <div>
                        <h1 className="font-display font-semibold text-2xl text-ink">{jobPosting?.title}</h1>
                        <p className="text-xs text-muted mt-0.5">{cvs.length} CV{cvs.length !== 1 ? 's' : ''} reçu{cvs.length !== 1 ? 's' : ''}</p>
                    </div>
                </div>
                <div className="flex items-center gap-2 shrink-0">
                    <Link to={`/job-postings/${id}/edit`}
                        className="inline-flex items-center gap-1.5 px-3 py-2 border border-line text-ink font-medium text-sm rounded-lg hover:bg-paper transition">
                        <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                            <path strokeLinecap="round" strokeLinejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Modifier
                    </Link>
                    <button onClick={() => setShowDeleteJob(true)}
                        className="inline-flex items-center gap-1.5 px-3 py-2 border border-danger/30 text-danger font-medium text-sm rounded-lg hover:bg-danger-soft transition">
                        <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                            <path strokeLinecap="round" strokeLinejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Supprimer
                    </button>
                    <button onClick={() => setShowUpload(true)}
                        className="inline-flex items-center gap-2 px-4 py-2 bg-signal text-white font-medium text-sm rounded-lg hover:bg-signal-dark transition">
                        <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                            <path strokeLinecap="round" strokeLinejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Ajouter des CVs
                    </button>
                </div>
            </div>

            {error && <div className="p-3 rounded-lg bg-danger-soft text-danger text-sm">{error}</div>}

            {/* Skills tags */}
            {(jobPosting?.required_skills ?? []).length > 0 && (
                <div className="bg-surface border border-line rounded-xl p-4">
                    <p className="text-xs font-semibold text-muted uppercase tracking-wider mb-2">Compétences recherchées</p>
                    <div className="flex flex-wrap gap-1.5">
                        {jobPosting?.required_skills.map((skill) => (
                            <span key={skill} className="text-xs font-mono text-signal bg-signal-soft px-2 py-1 rounded-md">{skill}</span>
                        ))}
                    </div>
                </div>
            )}

            {/* CVs Table */}
            <div className="bg-surface border border-line rounded-xl overflow-hidden">
                <div className="px-5 py-4 border-b border-line flex items-center justify-between gap-3 flex-wrap">
                    <h2 className="font-display font-semibold text-sm text-ink">Candidatures</h2>
                    <div className="relative">
                        <svg className="w-4 h-4 text-muted absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                            <path strokeLinecap="round" strokeLinejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input type="text" value={cvSearch} onChange={(e) => setCvSearch(e.target.value)}
                            placeholder="Rechercher un candidat..."
                            className="pl-9 pr-3 py-1.5 text-sm bg-paper border border-line rounded-lg focus:outline-none focus:ring-2 focus:ring-signal/30 focus:border-signal transition" />
                    </div>
                </div>

                {filteredAndSortedCvs.length === 0 ? (
                    <div className="px-5 py-12 text-center">
                        <p className="text-sm text-muted">{cvs.length === 0 ? "Aucun CV pour le moment. Ajoutez des CVs pour commencer l'analyse." : "Aucun résultat pour votre recherche."}</p>
                    </div>
                ) : (
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead>
                                <tr className="border-b border-line bg-paper/50">
                                    {([['candidate_name', 'Candidat'], ['analysis_status', 'Statut'], ['created_at', 'Date']] as [SortKey, string][]).map(([key, label]) => (
                                        <th key={key}
                                            className="px-5 py-3 text-left text-xs font-semibold text-muted uppercase tracking-wider cursor-pointer hover:text-ink transition select-none"
                                            onClick={() => handleSort(key)}>
                                            <span className="flex items-center gap-1">
                                                {label}
                                                {sortKey === key && <span className="text-signal">{sortDir === 'asc' ? '↑' : '↓'}</span>}
                                            </span>
                                        </th>
                                    ))}
                                    <th className="px-5 py-3 text-right text-xs font-semibold text-muted uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-line">
                                {filteredAndSortedCvs.map((cv) => (
                                    <tr key={cv.id} className="hover:bg-paper/50 transition group">
                                        <td className="px-5 py-3.5">
                                            <Link to={`/job-postings/${id}/cvs/${cv.id}`} className="font-medium text-ink hover:text-signal transition">
                                                {cv.candidate_name || 'Inconnu'}
                                            </Link>
                                            <p className="text-xs text-muted truncate max-w-xs">{cv.file_name}</p>
                                        </td>
                                        <td className="px-5 py-3.5">
                                            <StatusBadge status={cv.analysis_status} />
                                        </td>
                                        <td className="px-5 py-3.5 text-xs text-muted font-mono">
                                            {cv.created_at ? new Date(cv.created_at).toLocaleDateString('fr-FR') : '—'}
                                        </td>
                                        <td className="px-5 py-3.5 text-right">
                                            <div className="flex items-center justify-end gap-2">
                                                <Link to={`/job-postings/${id}/cvs/${cv.id}`}
                                                    className="text-xs text-signal hover:underline font-medium">
                                                    Voir l'analyse
                                                </Link>
                                                <button onClick={() => setCvToDelete(cv)}
                                                    className="text-xs text-muted hover:text-danger transition opacity-0 group-hover:opacity-100">
                                                    Supprimer
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </div>
        </div>
    );
}
