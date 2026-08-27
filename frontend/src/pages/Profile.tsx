import { useState, useEffect, FormEvent } from 'react';
import { useAuth } from '../context/AuthContext';
import client from '../api/client';

export default function Profile(): JSX.Element {
    const { user, updateUser } = useAuth();

    const [name, setName] = useState('');
    const [email, setEmail] = useState('');
    const [profileLoading, setProfileLoading] = useState(false);
    const [profileError, setProfileError] = useState<string | null>(null);
    const [profileSuccess, setProfileSuccess] = useState<string | null>(null);

    const [currentPassword, setCurrentPassword] = useState('');
    const [password, setPassword] = useState('');
    const [passwordConfirmation, setPasswordConfirmation] = useState('');
    const [passwordLoading, setPasswordLoading] = useState(false);
    const [passwordError, setPasswordError] = useState<string | null>(null);
    const [passwordSuccess, setPasswordSuccess] = useState<string | null>(null);

    useEffect(() => {
        if (user) {
            setName(user.name || '');
            setEmail(user.email || '');
        }
    }, [user]);

    const handleProfileSubmit = async (e: FormEvent<HTMLFormElement>): Promise<void> => {
        e.preventDefault();
        setProfileLoading(true);
        setProfileError(null);
        setProfileSuccess(null);
        try {
            const response = await client.put<{ data?: { name: string; email: string } } & { name: string; email: string }>('/me', { name, email });
            const data = response.data?.data ?? response.data;
            updateUser({ name: data.name, email: data.email });
            setProfileSuccess('Profil mis à jour avec succès.');
        } catch (err: unknown) {
            const axiosErr = err as { response?: { data?: { message?: string; errors?: { name?: string[]; email?: string[] } } } };
            const errors = axiosErr.response?.data?.errors;
            setProfileError(errors?.name?.[0] ?? errors?.email?.[0] ?? axiosErr.response?.data?.message ?? 'Erreur lors de la mise à jour du profil.');
        } finally {
            setProfileLoading(false);
        }
    };

    const handlePasswordSubmit = async (e: FormEvent<HTMLFormElement>): Promise<void> => {
        e.preventDefault();
        setPasswordLoading(true);
        setPasswordError(null);
        setPasswordSuccess(null);
        try {
            await client.put('/me/password', {
                current_password: currentPassword,
                password,
                password_confirmation: passwordConfirmation,
            });
            setPasswordSuccess('Mot de passe mis à jour avec succès.');
            setCurrentPassword('');
            setPassword('');
            setPasswordConfirmation('');
        } catch (err: unknown) {
            const axiosErr = err as { response?: { data?: { message?: string; errors?: { current_password?: string[]; password?: string[] } } } };
            const errors = axiosErr.response?.data?.errors;
            setPasswordError(errors?.current_password?.[0] ?? errors?.password?.[0] ?? axiosErr.response?.data?.message ?? 'Erreur lors du changement de mot de passe.');
        } finally {
            setPasswordLoading(false);
        }
    };

    return (
        <div className="max-w-2xl mx-auto space-y-6">
            <div>
                <h1 className="font-display font-semibold text-2xl text-ink">Mon Profil</h1>
                <p className="text-sm text-muted mt-1">Gérez vos informations personnelles et la sécurité de votre compte.</p>
            </div>

            {/* Section Profil */}
            <div className="bg-surface border border-line rounded-xl p-6 shadow-sm">
                <div className="flex items-center gap-3 mb-6 pb-4 border-b border-line">
                    <div className="w-10 h-10 rounded-full bg-signal-soft text-signal flex items-center justify-center font-display font-bold">
                        {name ? name.charAt(0).toUpperCase() : 'U'}
                    </div>
                    <div>
                        <h2 className="font-display font-semibold text-base text-ink">Informations du profil</h2>
                        <p className="text-xs text-muted">Modifiez votre nom et votre adresse email de connexion.</p>
                    </div>
                </div>

                {profileSuccess && (
                    <div className="mb-5 p-3.5 rounded-lg bg-match-soft text-match text-sm flex items-center gap-2">
                        <svg className="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                            <path strokeLinecap="round" strokeLinejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        {profileSuccess}
                    </div>
                )}

                {profileError && <div className="mb-5 p-3.5 rounded-lg bg-danger-soft text-danger text-sm">{profileError}</div>}

                <form onSubmit={handleProfileSubmit} className="space-y-4">
                    <div>
                        <label className="block text-sm font-medium text-ink mb-1.5">Nom complet</label>
                        <input type="text" required value={name} onChange={(e) => setName(e.target.value)}
                            className="w-full px-3.5 py-2.5 bg-paper border border-line rounded-lg text-sm text-ink focus:outline-none focus:ring-2 focus:ring-signal/30 focus:border-signal transition" />
                    </div>
                    <div>
                        <label className="block text-sm font-medium text-ink mb-1.5">Adresse email</label>
                        <input type="email" required value={email} onChange={(e) => setEmail(e.target.value)}
                            className="w-full px-3.5 py-2.5 bg-paper border border-line rounded-lg text-sm text-ink focus:outline-none focus:ring-2 focus:ring-signal/30 focus:border-signal transition" />
                    </div>
                    <div className="pt-2 flex justify-end">
                        <button type="submit" disabled={profileLoading}
                            className="px-5 py-2.5 bg-signal text-white font-medium text-sm rounded-lg hover:bg-signal-dark transition disabled:opacity-50">
                            {profileLoading ? 'Enregistrement...' : 'Enregistrer les modifications'}
                        </button>
                    </div>
                </form>
            </div>

            {/* Section Sécurité */}
            <div className="bg-surface border border-line rounded-xl p-6 shadow-sm">
                <div className="flex items-center gap-3 mb-6 pb-4 border-b border-line">
                    <div className="w-10 h-10 rounded-full bg-paper text-muted flex items-center justify-center">
                        <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.8}>
                            <path strokeLinecap="round" strokeLinejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <div>
                        <h2 className="font-display font-semibold text-base text-ink">Sécurité du compte</h2>
                        <p className="text-xs text-muted">Mettez à jour votre mot de passe d'accès.</p>
                    </div>
                </div>

                {passwordSuccess && (
                    <div className="mb-5 p-3.5 rounded-lg bg-match-soft text-match text-sm flex items-center gap-2">
                        <svg className="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                            <path strokeLinecap="round" strokeLinejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        {passwordSuccess}
                    </div>
                )}

                {passwordError && <div className="mb-5 p-3.5 rounded-lg bg-danger-soft text-danger text-sm">{passwordError}</div>}

                <form onSubmit={handlePasswordSubmit} className="space-y-4">
                    <div>
                        <label className="block text-sm font-medium text-ink mb-1.5">Mot de passe actuel</label>
                        <input type="password" required value={currentPassword} onChange={(e) => setCurrentPassword(e.target.value)}
                            className="w-full px-3.5 py-2.5 bg-paper border border-line rounded-lg text-sm text-ink focus:outline-none focus:ring-2 focus:ring-signal/30 focus:border-signal transition"
                            placeholder="••••••••" />
                    </div>
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-medium text-ink mb-1.5">Nouveau mot de passe</label>
                            <input type="password" required minLength={8} value={password} onChange={(e) => setPassword(e.target.value)}
                                className="w-full px-3.5 py-2.5 bg-paper border border-line rounded-lg text-sm text-ink focus:outline-none focus:ring-2 focus:ring-signal/30 focus:border-signal transition"
                                placeholder="••••••••" />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-ink mb-1.5">Confirmer le nouveau mot de passe</label>
                            <input type="password" required minLength={8} value={passwordConfirmation} onChange={(e) => setPasswordConfirmation(e.target.value)}
                                className="w-full px-3.5 py-2.5 bg-paper border border-line rounded-lg text-sm text-ink focus:outline-none focus:ring-2 focus:ring-signal/30 focus:border-signal transition"
                                placeholder="••••••••" />
                        </div>
                    </div>
                    <div className="pt-2 flex justify-end">
                        <button type="submit" disabled={passwordLoading}
                            className="px-5 py-2.5 bg-ink text-white font-medium text-sm rounded-lg hover:bg-ink/80 transition disabled:opacity-50">
                            {passwordLoading ? 'Mise à jour...' : 'Changer le mot de passe'}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}
