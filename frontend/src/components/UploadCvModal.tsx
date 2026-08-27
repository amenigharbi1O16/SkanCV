import { useState, useRef, useCallback, DragEvent, FormEvent } from 'react';
import client from '../api/client';

const MAX_SIZE_MB = 5;
const MAX_SIZE_BYTES = MAX_SIZE_MB * 1024 * 1024;

interface UploadCvModalProps {
    jobPostingId: number;
    onClose: () => void;
    onSuccess: () => void;
}

export default function UploadCvModal({ jobPostingId, onClose, onSuccess }: UploadCvModalProps): JSX.Element {
    const [files, setFiles] = useState<File[]>([]);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [uploadProgress, setUploadProgress] = useState(0);
    const [isDragging, setIsDragging] = useState(false);
    const inputRef = useRef<HTMLInputElement>(null);

    const addFiles = useCallback((incoming: FileList | null): void => {
        if (!incoming) return;
        const valid: File[] = [];
        const oversized: string[] = [];

        Array.from(incoming).forEach((file) => {
            if (file.size > MAX_SIZE_BYTES) {
                oversized.push(file.name);
            } else {
                valid.push(file);
            }
        });

        if (oversized.length > 0) {
            setError(`Fichier(s) trop lourd(s) (max ${MAX_SIZE_MB} Mo) : ${oversized.join(', ')}`);
        } else {
            setError(null);
        }

        setFiles((prev) => {
            const names = new Set(prev.map((f) => f.name));
            return [...prev, ...valid.filter((f) => !names.has(f.name))];
        });
    }, []);

    const removeFile = (name: string): void => {
        setFiles((prev) => prev.filter((f) => f.name !== name));
    };

    const handleDragOver = (e: DragEvent<HTMLDivElement>): void => {
        e.preventDefault();
        setIsDragging(true);
    };

    const handleDragLeave = (): void => setIsDragging(false);

    const handleDrop = (e: DragEvent<HTMLDivElement>): void => {
        e.preventDefault();
        setIsDragging(false);
        addFiles(e.dataTransfer.files);
    };

    const handleClose = (): void => {
        if (!loading) onClose();
    };

    const handleSubmit = async (e: FormEvent<HTMLFormElement>): Promise<void> => {
        e.preventDefault();
        if (files.length === 0) return;

        setLoading(true);
        setError(null);
        setUploadProgress(0);

        try {
            if (files.length === 1) {
                const formData = new FormData();
                formData.append('file', files[0]);
                await client.post(`/job-postings/${jobPostingId}/cvs`, formData, {
                    headers: { 'Content-Type': 'multipart/form-data' },
                    onUploadProgress: (e) => {
                        if (e.total) setUploadProgress(Math.round((e.loaded * 100) / e.total));
                    },
                });
            } else {
                const formData = new FormData();
                files.forEach((f) => formData.append('files[]', f));
                await client.post(`/job-postings/${jobPostingId}/cvs/batch`, formData, {
                    headers: { 'Content-Type': 'multipart/form-data' },
                    onUploadProgress: (e) => {
                        if (e.total) setUploadProgress(Math.round((e.loaded * 100) / e.total));
                    },
                });
            }
            onSuccess();
            onClose();
        } catch (err: unknown) {
            const axiosErr = err as { response?: { data?: { message?: string } } };
            setError(axiosErr.response?.data?.message || "Erreur lors de l'envoi des CVs.");
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-ink/50 backdrop-blur-sm p-4">
            <div className="bg-surface rounded-xl shadow-xl w-full max-w-lg animate-tag-reveal">
                <div className="flex items-center justify-between px-6 py-4 border-b border-line">
                    <h2 className="font-display font-semibold text-ink">Ajouter des CVs</h2>
                    <button
                        type="button"
                        onClick={handleClose}
                        disabled={loading}
                        className="text-muted hover:text-ink transition disabled:opacity-50"
                        aria-label="Fermer"
                    >
                        <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                            <path strokeLinecap="round" strokeLinejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form onSubmit={handleSubmit} className="p-6 space-y-4">
                    <div
                        onDragOver={handleDragOver}
                        onDragLeave={handleDragLeave}
                        onDrop={handleDrop}
                        onClick={() => !loading && inputRef.current?.click()}
                        className={`border-2 border-dashed rounded-xl p-8 text-center cursor-pointer transition ${
                            isDragging
                                ? 'border-signal bg-signal-soft/30'
                                : 'border-line hover:border-signal/40 hover:bg-paper/50'
                        } ${loading ? 'cursor-not-allowed opacity-60' : ''}`}
                    >
                        <svg className="w-8 h-8 mx-auto text-muted/60 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.5}>
                            <path strokeLinecap="round" strokeLinejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        <p className="text-sm font-medium text-ink">
                            Glissez vos PDF ici ou <span className="text-signal underline">cliquez pour parcourir</span>
                        </p>
                        <p className="text-xs text-muted mt-1.5">
                            Formats acceptés : PDF · Max {MAX_SIZE_MB} Mo par fichier · Détection automatique des compétences par l'IA
                        </p>
                        <input
                            ref={inputRef}
                            type="file"
                            accept="application/pdf"
                            multiple
                            className="hidden"
                            onChange={(e) => addFiles(e.target.files)}
                        />
                    </div>

                    {error && (
                        <div className="p-3 rounded-lg bg-danger-soft text-danger text-xs leading-relaxed flex items-start gap-2">
                            <svg className="w-4 h-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                                <path strokeLinecap="round" strokeLinejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>{error}</span>
                        </div>
                    )}

                    {files.length > 0 && (
                        <div className="space-y-2">
                            <div className="flex items-center justify-between text-xs font-semibold text-muted uppercase tracking-wider">
                                <span>Fichiers prêts ({files.length})</span>
                                {!loading && (
                                    <button
                                        type="button"
                                        onClick={() => setFiles([])}
                                        className="text-danger hover:underline text-[11px] font-medium"
                                    >
                                        Tout retirer
                                    </button>
                                )}
                            </div>

                            <ul className="space-y-2 max-h-40 overflow-y-auto pr-1">
                                {files.map((file) => (
                                    <li
                                        key={file.name}
                                        className="flex items-center justify-between gap-3 px-3 py-2 rounded-lg text-xs bg-paper border border-line"
                                    >
                                        <div className="flex items-center gap-2 min-w-0">
                                            <svg className="w-4 h-4 shrink-0 text-danger" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                                                <path strokeLinecap="round" strokeLinejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <span className="truncate text-ink font-medium">{file.name}</span>
                                            <span className="shrink-0 text-muted font-mono">
                                                {(file.size / 1024 / 1024).toFixed(2)} Mo
                                            </span>
                                        </div>
                                        {!loading && (
                                            <button
                                                type="button"
                                                onClick={() => removeFile(file.name)}
                                                className="shrink-0 w-5 h-5 flex items-center justify-center rounded-full text-muted hover:bg-danger-soft hover:text-danger transition"
                                                aria-label="Supprimer"
                                            >
                                                <svg className="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2.5}>
                                                    <path strokeLinecap="round" strokeLinejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        )}
                                    </li>
                                ))}
                            </ul>
                        </div>
                    )}

                    {loading && (
                        <div className="space-y-1.5 pt-2">
                            <div className="flex items-center justify-between text-xs text-muted">
                                <span className="font-medium text-ink">Téléchargement en cours...</span>
                                <span className="font-mono font-semibold text-signal">{uploadProgress}%</span>
                            </div>
                            <div className="h-2 w-full bg-paper rounded-full overflow-hidden border border-line">
                                <div
                                    className="h-full bg-signal transition-all duration-200 rounded-full"
                                    style={{ width: `${uploadProgress}%` }}
                                />
                            </div>
                        </div>
                    )}

                    <div className="flex justify-end gap-3 pt-3 border-t border-line">
                        <button
                            type="button"
                            disabled={loading}
                            onClick={handleClose}
                            className="px-4 py-2 text-muted hover:text-ink font-medium text-sm transition"
                        >
                            Annuler
                        </button>
                        <button
                            type="submit"
                            disabled={loading || files.length === 0}
                            className="px-5 py-2 bg-signal text-white font-medium text-sm rounded-lg hover:bg-signal-dark transition disabled:opacity-50 flex items-center gap-2"
                        >
                            {loading && (
                                <svg className="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                                    <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                                </svg>
                            )}
                            {loading
                                ? 'Envoi en cours...'
                                : files.length === 0
                                    ? 'Sélectionner des CVs'
                                    : files.length === 1
                                        ? 'Soumettre 1 CV'
                                        : `Soumettre ${files.length} CVs`}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}
