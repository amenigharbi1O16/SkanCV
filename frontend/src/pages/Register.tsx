import { useState, FormEvent } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

const SAMPLE_SKILLS = ['Python', 'Laravel', 'React', 'Docker', 'FastAPI'];

export default function Register(): JSX.Element {
    const { register } = useAuth();
    const navigate = useNavigate();

    const [name, setName] = useState('');
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [passwordConfirmation, setPasswordConfirmation] = useState('');
    const [error, setError] = useState<string | null>(null);
    const [isSubmitting, setIsSubmitting] = useState(false);

    async function handleSubmit(e: FormEvent<HTMLFormElement>): Promise<void> {
        e.preventDefault();
        setError(null);
        setIsSubmitting(true);

        try {
            await register(name, email, password, passwordConfirmation);
            navigate('/', { replace: true });
        } catch (err: unknown) {
            const axiosErr = err as { response?: { data?: { errors?: { email?: string[]; password?: string[] }; message?: string } } };
            const errors = axiosErr.response?.data?.errors;
            const message =
                errors?.email?.[0] ??
                errors?.password?.[0] ??
                axiosErr.response?.data?.message ??
                'Une erreur est survenue. Réessayez.';
            setError(message);
        } finally {
            setIsSubmitting(false);
        }
    }

    return (
        <div className="min-h-screen flex">
            <div className="hidden lg:flex lg:w-1/2 bg-ink relative overflow-hidden flex-col justify-between p-12">
                <span className="font-display font-bold text-xl text-white tracking-tight">SkanCV</span>

                <div className="relative z-10 max-w-sm">
                    <h1 className="font-display font-semibold text-3xl text-white leading-tight mb-3">
                        Rejoignez SkanCV.
                    </h1>
                    <p className="text-white/60 text-sm leading-relaxed">
                        Optimisez vos processus de recrutement grâce à notre IA qui
                        extrait et compare automatiquement les compétences des candidats
                        avec vos exigences.
                    </p>
                </div>

                <div className="relative z-10 mt-10 w-full max-w-xs">
                    <div className="relative bg-white/5 border border-white/10 rounded-lg p-5 overflow-hidden">
                        <div className="space-y-2 mb-4">
                            <div className="h-2 w-3/4 bg-white/15 rounded" />
                            <div className="h-2 w-full bg-white/10 rounded" />
                            <div className="h-2 w-5/6 bg-white/10 rounded" />
                            <div className="h-2 w-2/3 bg-white/10 rounded" />
                        </div>
                        <div className="flex flex-wrap gap-1.5">
                            {SAMPLE_SKILLS.map((skill, i) => (
                                <span
                                    key={skill}
                                    style={{ animationDelay: `${0.6 + i * 0.3}s` }}
                                    className="animate-tag-reveal opacity-0 text-xs font-mono text-signal bg-signal/15 px-2 py-1 rounded-md"
                                >
                                    {skill}
                                </span>
                            ))}
                        </div>
                        <div className="absolute inset-x-0 top-0 h-10 bg-gradient-to-b from-match/50 to-transparent animate-scan-sweep" />
                    </div>
                </div>
            </div>

            <div className="w-full lg:w-1/2 flex items-center justify-center bg-paper px-6 py-12 overflow-y-auto">
                <form onSubmit={handleSubmit} className="w-full max-w-sm">
                    <div className="lg:hidden mb-8 text-center">
                        <span className="font-display font-bold text-2xl text-ink">SkanCV</span>
                    </div>

                    <h2 className="font-display font-semibold text-2xl text-ink mb-1">Créer un compte</h2>
                    <p className="text-sm text-muted mb-6">Commencez à recruter plus intelligemment.</p>

                    {error && (
                        <div className="mb-4 p-3 rounded-lg bg-danger-soft text-danger text-sm">
                            {error}
                        </div>
                    )}

                    <div className="mb-4">
                        <label htmlFor="name" className="block text-sm font-medium text-ink mb-1.5">Nom complet</label>
                        <input id="name" type="text" required value={name} onChange={(e) => setName(e.target.value)}
                            className="w-full px-3.5 py-2.5 bg-surface border border-line rounded-lg text-sm text-ink placeholder:text-muted/60 focus:outline-none focus:ring-2 focus:ring-signal/30 focus:border-signal transition"
                            placeholder="Jean Dupont" />
                    </div>

                    <div className="mb-4">
                        <label htmlFor="email" className="block text-sm font-medium text-ink mb-1.5">Email</label>
                        <input id="email" type="email" required value={email} onChange={(e) => setEmail(e.target.value)}
                            autoComplete="email"
                            className="w-full px-3.5 py-2.5 bg-surface border border-line rounded-lg text-sm text-ink placeholder:text-muted/60 focus:outline-none focus:ring-2 focus:ring-signal/30 focus:border-signal transition"
                            placeholder="vous@entreprise.com" />
                    </div>

                    <div className="mb-4">
                        <label htmlFor="password" className="block text-sm font-medium text-ink mb-1.5">Mot de passe</label>
                        <input id="password" type="password" required value={password} onChange={(e) => setPassword(e.target.value)}
                            autoComplete="new-password"
                            className="w-full px-3.5 py-2.5 bg-surface border border-line rounded-lg text-sm text-ink placeholder:text-muted/60 focus:outline-none focus:ring-2 focus:ring-signal/30 focus:border-signal transition"
                            placeholder="••••••••" />
                    </div>

                    <div className="mb-6">
                        <label htmlFor="password_confirmation" className="block text-sm font-medium text-ink mb-1.5">Confirmer le mot de passe</label>
                        <input id="password_confirmation" type="password" required value={passwordConfirmation}
                            onChange={(e) => setPasswordConfirmation(e.target.value)}
                            autoComplete="new-password"
                            className="w-full px-3.5 py-2.5 bg-surface border border-line rounded-lg text-sm text-ink placeholder:text-muted/60 focus:outline-none focus:ring-2 focus:ring-signal/30 focus:border-signal transition"
                            placeholder="••••••••" />
                    </div>

                    <button type="submit" disabled={isSubmitting}
                        className="w-full py-2.5 px-4 bg-signal hover:bg-signal-dark text-white text-sm font-medium rounded-lg disabled:opacity-50 disabled:cursor-not-allowed transition mb-4">
                        {isSubmitting ? 'Création en cours...' : "S'inscrire"}
                    </button>

                    <div className="text-center">
                        <p className="text-sm text-muted">
                            Déjà un compte?{' '}
                            <Link to="/login" className="text-signal hover:text-signal-dark font-medium transition">Se connecter</Link>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    );
}
