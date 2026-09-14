import { useState } from 'react';
import { Link, useLocation, useNavigate, useSearchParams } from 'react-router-dom';
import GoogleButton from '../components/GoogleButton';
import { ApiError } from '../lib/api';
import { useAuth } from '../lib/auth';
import { GOOGLE_AUTH_ERROR_MESSAGES } from '../lib/googleAuthErrors';

export default function LoginPage() {
    const { login } = useAuth();
    const navigate = useNavigate();
    const location = useLocation();
    const [searchParams] = useSearchParams();
    const oauthError = searchParams.get('error');
    const [form, setForm] = useState({ email: '', password: '' });
    const [error, setError] = useState(
        oauthError ? (GOOGLE_AUTH_ERROR_MESSAGES[oauthError] ?? 'No pudimos completar el acceso con Google.') : '',
    );
    const [isSubmitting, setIsSubmitting] = useState(false);

    const handleSubmit = async (event) => {
        event.preventDefault();
        setError('');
        setIsSubmitting(true);

        try {
            await login(form.email, form.password);
            navigate(location.state?.from?.pathname ?? '/cuenta', { replace: true });
        } catch (submitError) {
            setError(submitError instanceof ApiError ? submitError.message : 'No pudimos iniciar sesión.');
        } finally {
            setIsSubmitting(false);
        }
    };

    return (
        <div className="mx-auto max-w-md px-4 py-24 sm:px-6">
            <h1 className="text-center text-2xl font-bold text-white">Ingresar</h1>

            <div className="mt-8 space-y-4">
                <GoogleButton />
                <div className="flex items-center gap-3 text-xs uppercase text-white/50">
                    <span className="h-px flex-1 bg-white/10" /> o con tu correo <span className="h-px flex-1 bg-white/10" />
                </div>
            </div>

            <form onSubmit={handleSubmit} className="mt-6 space-y-4">
                <div className="contact-form-field">
                    <label className="text-sm text-white/70">Correo electrónico</label>
                    <input required type="email" className="contact-input" value={form.email} onChange={(e) => setForm({ ...form, email: e.target.value })} />
                </div>
                <div className="contact-form-field">
                    <label className="text-sm text-white/70">Contraseña</label>
                    <input required type="password" className="contact-input" value={form.password} onChange={(e) => setForm({ ...form, password: e.target.value })} />
                </div>

                {error && <p className="text-sm text-red-400">{error}</p>}

                <button type="submit" disabled={isSubmitting} className="btn-primary w-full">
                    {isSubmitting ? 'Ingresando…' : 'Ingresar'}
                </button>
            </form>

            <p className="mt-6 text-center text-sm text-white/60">
                ¿No tienes cuenta? <Link to="/registro" className="text-brand">Regístrate gratis</Link>
            </p>
        </div>
    );
}
