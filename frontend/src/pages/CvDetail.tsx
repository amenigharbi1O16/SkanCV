import { useEffect, useState } from 'react';
import { useParams, Link } from 'react-router-dom';
import client from '../api/client';
import StatusBadge from '../components/StatusBadge';
import type { Cv, Analysis } from '../types';

export default function CvDetail(): JSX.Element {
    const { jobPostingId, cvId } = useParams<{ jobPostingId: string; cvId: string }>();
    const [cv, setCv] = useState<Cv | null>(null);
    const [analysis, setAnalysis] = useState<Analysis | null>(null);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        const loadData = async (): Promise<void> => {
            try {
                const [cvRes, analysisRes] = await Promise.all([
                    client.get<{ data?: Cv } | Cv>(`/job-postings/${jobPostingId}/cvs/${cvId}`),
                    client.get<{ data?: Analysis } | Analysis>(`/job-postings/${jobPostingId}/cvs/${cvId}/analysis`).catch(() => null),
                ]);
                const cvData = cvRes.data as { data?: Cv } & Cv;
                setCv(cvData.data ?? cvData);
                if (analysisRes) {
                    const aData = analysisRes.data as { data?: Analysis } & Analysis;
                    setAnalysis(aData.data ?? aData);
                }
            } catch {
                setError('Erreur lors du chargement des données du CV.');
            }
        };
        loadData();
    }, [jobPostingId, cvId]);

    useEffect(() => {
        const status = analysis?.status || cv?.analysis_status;
        if (status !== 'pending' && status !== 'processing') return undefined;

        const interval = setInterval(async () => {
            try {
                const [cvRes, analysisRes] = await Promise.all([
                    client.get<{ data?: Cv } | Cv>(`/job-postings/${jobPostingId}/cvs/${cvId}`),
                    client.get<{ data?: Analysis } | Analysis>(`/job-postings/${jobPostingId}/cvs/${cvId}/analysis`).catch(() => null),
                ]);
                const cvData = cvRes.data as { data?: Cv } & Cv;
                setCv(cvData.data ?? cvData);
                if (analysisRes) {
                    const aData = analysisRes.data as { data?: Analysis } & Analysis;
                    setAnalysis(aData.data ?? aData);
                }
            } catch {
                // ignore polling errors
            }
        }, 5000);

        return () => clearInterval(interval);
    }, [analysis?.status, cv?.analysis_status, jobPostingId, cvId]);

    const handlePrint = (): void => {
        window.print();
    };

    if (error) {
        return (
            <div className="space-y-4">
                <div className="p-4 rounded-lg bg-danger-soft text-danger text-sm">{error}</div>
                <Link to={`/job-postings/${jobPostingId}`} className="text-signal hover:underline text-sm font-medium">
                    ← Retour à l'offre
                </Link>
            </div>
        );
    }

    if (!cv) {
        return (
            <div className="space-y-6">
                <div className="h-24 bg-surface border border-line rounded-xl animate-pulse" />
                <div className="h-64 bg-surface border border-line rounded-xl animate-pulse" />
            </div>
        );
    }

    const isMatch = (analysis?.similarity_score ?? 0) >= 0.7;
    const isWarn = (analysis?.similarity_score ?? 0) >= 0.4 && (analysis?.similarity_score ?? 0) < 0.7;

    return (
        <div className="max-w-4xl mx-auto space-y-6 print:m-0 print:p-0 print:max-w-full">
            {/* Header */}
            <div className="flex items-center justify-between gap-4 print:hidden">
                <div className="flex items-center gap-3">
                    <Link to={`/job-postings/${jobPostingId}`} className="text-muted hover:text-ink transition">
                        <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                            <path strokeLinecap="round" strokeLinejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </Link>
                    <div>
                        <h1 className="font-display font-semibold text-2xl text-ink">Candidature : {cv.candidate_name || 'Inconnu'}</h1>
                        <p className="text-xs text-muted">Évaluation et analyse automatique par l'IA SkanCV.</p>
                    </div>
                </div>

                <button
                    onClick={handlePrint}
                    className="inline-flex items-center gap-2 px-4 py-2 bg-surface border border-line text-ink font-medium text-sm rounded-lg hover:bg-paper transition shadow-sm"
                >
                    <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.8}>
                        <path strokeLinecap="round" strokeLinejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Imprimer / Exporter PDF
                </button>
            </div>

            {/* CV Info Card */}
            <div className="bg-surface border border-line rounded-xl p-5 flex items-center gap-4">
                <div className="w-10 h-10 rounded-full bg-signal-soft text-signal flex items-center justify-center font-display font-bold shrink-0">
                    {(cv.candidate_name || 'I').charAt(0).toUpperCase()}
                </div>
                <div className="flex-1 min-w-0">
                    <p className="font-display font-semibold text-ink">{cv.candidate_name || 'Inconnu'}</p>
                    <p className="text-xs text-muted truncate">{cv.file_name}</p>
                </div>
                <StatusBadge status={cv.analysis_status} />
            </div>

            {/* Score Card */}
            {analysis && analysis.status === 'completed' && (
                <div className={`rounded-xl p-6 border ${isMatch ? 'bg-match-soft border-match/30' : isWarn ? 'bg-warn-soft border-warn/30' : 'bg-danger-soft border-danger/30'}`}>
                    <div className="flex items-center justify-between mb-3">
                        <h2 className="font-display font-semibold text-ink">Score de compatibilité</h2>
                        <span className={`text-3xl font-display font-bold ${isMatch ? 'text-match' : isWarn ? 'text-warn' : 'text-danger'}`}>
                            {Math.round((analysis.similarity_score ?? 0) * 100)}%
                        </span>
                    </div>
                    <div className="h-2.5 bg-white/50 rounded-full overflow-hidden">
                        <div
                            className={`h-full rounded-full transition-all duration-500 ${isMatch ? 'bg-match' : isWarn ? 'bg-warn' : 'bg-danger'}`}
                            style={{ width: `${Math.round((analysis.similarity_score ?? 0) * 100)}%` }}
                        />
                    </div>
                    {analysis.summary && (
                        <p className="text-sm text-ink mt-4 leading-relaxed">{analysis.summary}</p>
                    )}
                </div>
            )}

            {/* Skills Analysis */}
            {analysis && analysis.status === 'completed' && (
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {(analysis.matched_skills ?? []).length > 0 && (
                        <div className="bg-surface border border-line rounded-xl p-5">
                            <h3 className="font-display font-semibold text-sm text-ink mb-3 flex items-center gap-2">
                                <span className="w-2 h-2 rounded-full bg-match" />
                                Compétences présentes ({analysis.matched_skills?.length})
                            </h3>
                            <div className="flex flex-wrap gap-1.5">
                                {analysis.matched_skills?.map((skill) => (
                                    <span key={skill} className="text-xs font-mono text-match bg-match-soft px-2 py-1 rounded-md">{skill}</span>
                                ))}
                            </div>
                        </div>
                    )}

                    {(analysis.missing_skills ?? []).length > 0 && (
                        <div className="bg-surface border border-line rounded-xl p-5">
                            <h3 className="font-display font-semibold text-sm text-ink mb-3 flex items-center gap-2">
                                <span className="w-2 h-2 rounded-full bg-danger" />
                                Compétences manquantes ({analysis.missing_skills?.length})
                            </h3>
                            <div className="flex flex-wrap gap-1.5">
                                {analysis.missing_skills?.map((skill) => (
                                    <span key={skill} className="text-xs font-mono text-danger bg-danger-soft px-2 py-1 rounded-md">{skill}</span>
                                ))}
                            </div>
                        </div>
                    )}
                </div>
            )}

            {/* Pending/Processing state */}
            {cv.analysis_status === 'pending' || cv.analysis_status === 'processing' ? (
                <div className="bg-surface border border-line rounded-xl p-8 text-center">
                    <div className="w-10 h-10 mx-auto mb-3 border-2 border-signal border-t-transparent rounded-full animate-spin" />
                    <p className="text-sm font-medium text-ink">Analyse en cours...</p>
                    <p className="text-xs text-muted mt-1">L'IA analyse ce CV. La page se mettra à jour automatiquement.</p>
                </div>
            ) : null}

            {/* Raw text */}
            {analysis?.raw_text && (
                <div className="bg-surface border border-line rounded-xl p-5">
                    <h3 className="font-display font-semibold text-sm text-ink mb-3">Texte extrait du CV</h3>
                    <pre className="text-xs text-muted whitespace-pre-wrap font-mono leading-relaxed max-h-64 overflow-y-auto">
                        {analysis.raw_text}
                    </pre>
                </div>
            )}
        </div>
    );
}
