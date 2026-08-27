import { useEffect, useRef, useState, useCallback } from 'react';
import client from '../api/client';
import StatusBadge from './StatusBadge';
import type { Notification } from '../types';

function timeAgo(isoString: string): string {
    const diffMs = Date.now() - new Date(isoString).getTime();
    const minutes = Math.floor(diffMs / 60000);
    if (minutes < 1) return "à l'instant";
    if (minutes < 60) return `il y a ${minutes} min`;
    const hours = Math.floor(minutes / 60);
    if (hours < 24) return `il y a ${hours} h`;
    return `il y a ${Math.floor(hours / 24)} j`;
}

export default function NotificationBell(): JSX.Element {
    const [notifications, setNotifications] = useState<Notification[]>([]);
    const [isOpen, setIsOpen] = useState(false);
    const [markingAll, setMarkingAll] = useState(false);
    const panelRef = useRef<HTMLDivElement>(null);

    const fetchUnread = useCallback(async (): Promise<void> => {
        try {
            const response = await client.get<Notification[]>('/notifications/unread');
            setNotifications(response.data || []);
        } catch {
            // Silencieux : une notif ratée ne doit pas casser l'UI
        }
    }, []);

    useEffect(() => {
        fetchUnread();
        const interval = setInterval(fetchUnread, 12000);
        return () => clearInterval(interval);
    }, [fetchUnread]);

    useEffect(() => {
        function handleClickOutside(e: MouseEvent): void {
            if (panelRef.current && !panelRef.current.contains(e.target as Node)) {
                setIsOpen(false);
            }
        }
        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    async function markAsRead(id: string): Promise<void> {
        setNotifications((prev) => prev.filter((n) => n.id !== id));
        try {
            await client.patch(`/notifications/${id}/read`);
        } catch {
            fetchUnread();
        }
    }

    async function markAllAsRead(): Promise<void> {
        setMarkingAll(true);
        try {
            await client.patch('/notifications/read-all');
            setNotifications([]);
        } catch {
            fetchUnread();
        } finally {
            setMarkingAll(false);
        }
    }

    return (
        <div className="relative" ref={panelRef}>
            <button
                onClick={() => setIsOpen((v) => !v)}
                className="relative p-2 rounded-lg hover:bg-paper transition"
                aria-label="Notifications"
            >
                <svg className="w-5 h-5 text-ink" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.8}>
                    <path strokeLinecap="round" strokeLinejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                {notifications.length > 0 && (
                    <span className="absolute -top-0.5 -right-0.5 flex h-4.5 w-4.5 items-center justify-center rounded-full bg-danger text-[10px] font-semibold text-white">
                        {notifications.length > 9 ? '9+' : notifications.length}
                    </span>
                )}
            </button>

            {isOpen && (
                <div className="absolute right-0 mt-2 w-80 sm:w-96 bg-surface border border-line rounded-xl shadow-xl overflow-hidden z-50 animate-tag-reveal">
                    <div className="px-4 py-3 border-b border-line flex items-center justify-between">
                        <div className="flex items-center gap-2">
                            <h3 className="font-display font-semibold text-sm text-ink">Notifications</h3>
                            {notifications.length > 0 && (
                                <span className="bg-signal-soft text-signal text-[11px] font-medium px-2 py-0.5 rounded-full">
                                    {notifications.length} non lue{notifications.length > 1 ? 's' : ''}
                                </span>
                            )}
                        </div>
                        {notifications.length > 0 && (
                            <button
                                onClick={markAllAsRead}
                                disabled={markingAll}
                                className="text-xs text-signal hover:underline font-medium disabled:opacity-50"
                            >
                                Tout marquer comme lu
                            </button>
                        )}
                    </div>
                    <div className="max-h-96 overflow-y-auto divide-y divide-line">
                        {notifications.length === 0 ? (
                            <div className="px-4 py-8 text-center">
                                <svg className="w-8 h-8 mx-auto text-muted/40 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M5 13l4 4L19 7" />
                                </svg>
                                <p className="text-xs font-medium text-ink">Vous êtes à jour !</p>
                                <p className="text-[11px] text-muted mt-0.5">Aucune nouvelle notification d'analyse.</p>
                            </div>
                        ) : (
                            notifications.map((n) => (
                                <button
                                    key={n.id}
                                    onClick={() => markAsRead(n.id)}
                                    className="w-full text-left px-4 py-3 hover:bg-paper transition"
                                >
                                    <div className="flex items-center justify-between mb-1.5">
                                        <StatusBadge status={n.data?.status || 'completed'} />
                                        <span className="text-[11px] text-muted">{timeAgo(n.created_at)}</span>
                                    </div>
                                    <p className="text-xs text-ink leading-snug font-medium">{n.data?.message || 'Mise à jour du CV'}</p>
                                </button>
                            ))
                        )}
                    </div>
                </div>
            )}
        </div>
    );
}
