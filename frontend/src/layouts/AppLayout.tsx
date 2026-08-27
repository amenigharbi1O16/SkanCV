import { useState, useEffect } from 'react';
import { Link, NavLink, Outlet, useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import NotificationBell from '../components/NotificationBell';
import client from '../api/client';
import type { JobPosting } from '../types';

export default function AppLayout(): JSX.Element {
    const { user, logout } = useAuth();
    const navigate = useNavigate();
    const [jobPostings, setJobPostings] = useState<JobPosting[]>([]);
    const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);

    const fetchJobPostings = async (): Promise<void> => {
        try {
            const response = await client.get<JobPosting[] | { data: JobPosting[] }>('/job-postings');
            const data = response.data as { data?: JobPosting[] } & JobPosting[];
            const list = Array.isArray(response.data) ? response.data : data.data;
            setJobPostings(list ?? []);
        } catch (err) {
            console.error('Failed to load job postings', err);
        }
    };

    useEffect(() => {
        fetchJobPostings();

        const handleUpdate = (): void => { fetchJobPostings(); };
        window.addEventListener('job-postings-updated', handleUpdate);
        return () => window.removeEventListener('job-postings-updated', handleUpdate);
    }, []);

    async function handleLogout(): Promise<void> {
        await logout();
        navigate('/login', { replace: true });
    }

    const userInitial = user?.name ? user.name.charAt(0).toUpperCase() : 'U';

    return (
        <div className="min-h-screen flex bg-paper">
            {/* Overlay Mobile */}
            {isMobileMenuOpen && (
                <div
                    className="fixed inset-0 z-40 bg-ink/50 backdrop-blur-xs lg:hidden"
                    onClick={() => setIsMobileMenuOpen(false)}
                />
            )}

            {/* Sidebar (Desktop fixe + Mobile tiroir) */}
            <aside
                className={`fixed lg:static inset-y-0 left-0 z-50 w-64 shrink-0 bg-ink text-white flex flex-col transform transition-transform duration-300 ease-in-out lg:translate-x-0 ${
                    isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full'
                }`}
            >
                <div className="px-6 py-5 border-b border-white/10 flex items-center justify-between">
                    <Link to="/" className="font-display font-bold text-xl tracking-tight text-white">
                        SkanCV
                    </Link>
                    <button
                        onClick={() => setIsMobileMenuOpen(false)}
                        className="lg:hidden text-white/60 hover:text-white p-1"
                        aria-label="Fermer le menu"
                    >
                        <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                            <path strokeLinecap="round" strokeLinejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <nav className="flex-1 px-3 py-4 space-y-4 overflow-y-auto">
                    <div>
                        <NavLink
                            to="/"
                            end
                            onClick={() => setIsMobileMenuOpen(false)}
                            className={({ isActive }) =>
                                `flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition ${
                                    isActive
                                        ? 'bg-signal text-white'
                                        : 'text-white/70 hover:bg-white/5 hover:text-white'
                                }`
                            }
                        >
                            <svg className="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.8}>
                                <path strokeLinecap="round" strokeLinejoin="round" d="M4 6h16M4 12h16M4 18h7" />
                            </svg>
                            Toutes les offres
                        </NavLink>
                    </div>

                    {jobPostings.length > 0 && (
                        <div className="space-y-1">
                            <div className="px-3 text-[11px] font-semibold text-white/40 uppercase tracking-wider mb-2">
                                Vos Offres ({jobPostings.length})
                            </div>
                            {jobPostings.map((jp) => (
                                <NavLink
                                    key={jp.id}
                                    to={`/job-postings/${jp.id}`}
                                    onClick={() => setIsMobileMenuOpen(false)}
                                    className={({ isActive }) =>
                                        `flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-medium transition truncate ${
                                            isActive
                                                ? 'bg-signal/20 text-signal-soft font-semibold'
                                                : 'text-white/60 hover:bg-white/5 hover:text-white'
                                        }`
                                    }
                                >
                                    <span className="truncate w-full">{jp.title}</span>
                                </NavLink>
                            ))}
                        </div>
                    )}
                </nav>

                {/* Footer Sidebar : Profil & Déconnexion */}
                <div className="p-3 border-t border-white/10 space-y-1">
                    <NavLink
                        to="/profile"
                        onClick={() => setIsMobileMenuOpen(false)}
                        className={({ isActive }) =>
                            `flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition ${
                                isActive
                                    ? 'bg-white/10 text-white'
                                    : 'text-white/80 hover:bg-white/5 hover:text-white'
                            }`
                        }
                    >
                        <div className="w-6 h-6 rounded-full bg-signal text-white text-xs font-display font-bold flex items-center justify-center shrink-0">
                            {userInitial}
                        </div>
                        <div className="min-w-0 flex-1">
                            <p className="text-xs font-medium text-white truncate">{user?.name || 'Mon Profil'}</p>
                            <p className="text-[10px] text-white/50 truncate">{user?.email || 'Paramètres'}</p>
                        </div>
                    </NavLink>

                    <button
                        onClick={handleLogout}
                        className="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-medium text-white/60 hover:bg-danger-soft/20 hover:text-danger transition"
                    >
                        <svg className="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.8}>
                            <path strokeLinecap="round" strokeLinejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 5v1a3 3 0 01-3 3H6a3 3 0 01-3-3V6a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Se déconnecter
                    </button>
                </div>
            </aside>

            {/* Zone Principale */}
            <div className="flex-1 flex flex-col min-w-0">
                <header className="h-16 shrink-0 bg-surface border-b border-line flex items-center justify-between px-4 sm:px-6 print:hidden">
                    <button
                        onClick={() => setIsMobileMenuOpen(true)}
                        className="lg:hidden p-2 rounded-lg text-ink hover:bg-paper transition"
                        aria-label="Ouvrir le menu"
                    >
                        <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                            <path strokeLinecap="round" strokeLinejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <div className="flex-1" />

                    <div className="flex items-center gap-3">
                        <NotificationBell />
                    </div>
                </header>

                <main className="flex-1 overflow-y-auto p-4 sm:p-8 print:p-0 print:overflow-visible">
                    <Outlet />
                </main>
            </div>
        </div>
    );
}
