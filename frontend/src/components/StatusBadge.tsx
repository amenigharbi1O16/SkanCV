/**
 * StatusBadge.tsx — Pilule de statut pour les 4 états du pipeline
 * d'analyse (voir App\Enums\AnalysisStatus côté Laravel).
 */
import type { AnalysisStatus } from '../types';

interface StatusConfig {
    label: string;
    bg: string;
    text: string;
    dot: string;
}

const STATUS_MAP: Record<AnalysisStatus, StatusConfig> = {
    pending:    { label: 'En attente',  bg: 'bg-warn-soft',   text: 'text-warn',   dot: 'bg-warn' },
    processing: { label: 'En cours',    bg: 'bg-signal-soft', text: 'text-signal', dot: 'bg-signal' },
    completed:  { label: 'Terminée',    bg: 'bg-match-soft',  text: 'text-match',  dot: 'bg-match' },
    failed:     { label: 'Échouée',     bg: 'bg-danger-soft', text: 'text-danger', dot: 'bg-danger' },
};

interface StatusBadgeProps {
    status: string;
}

export default function StatusBadge({ status }: StatusBadgeProps): JSX.Element {
    const config = STATUS_MAP[status as AnalysisStatus] ?? STATUS_MAP.pending;

    return (
        <span className={`relative inline-flex items-center gap-1.5 overflow-hidden px-2.5 py-1 rounded-full text-xs font-medium ${config.bg} ${config.text}`}>
            <span className={`h-1.5 w-1.5 rounded-full ${config.dot}`} />
            {config.label}
            {status === 'processing' && (
                <span className="absolute inset-0 pointer-events-none">
                    <span className="absolute top-0 h-full w-4 bg-white/40 animate-scan-mini" />
                </span>
            )}
        </span>
    );
}
