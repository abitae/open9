import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import GoogleButton from '../components/GoogleButton';
import { ApiError } from '../lib/api';
import { useAuth } from '../lib/auth';
import { useSite } from '../lib/site';

export default function RegisterPage() {
    const { register } = useAuth();
    const { site } = useSite();
    const navigate = useNavigate();
    const [form, setForm] = useState({ name: '', email: '', password: '', phone: '' });
    const [errors, setErrors] = useState({});
    const [error, setError] = useState('');
    const [isSubmitting, setIsSubmitting] = useState(false);

    const handleSubmit = async (event) => {
        event.preventDefault();
        setError('');
        setErrors({});
        setIsSubmitting(true);

        try {
            await register(form);
            navigate('/cuenta', { replace: true });
        } catch (submitError) {
            if (submitError instanceof ApiError) {
                setError(submitError.message);
                setErrors(submitError.errors ?? {});
            } else {
                setError('No pudimos crear tu cuenta.');
            }
        } finally {
            setIsSubmitting(false);
        }
    };

    return (
        <div className="mx-auto max-w-md px-4 py-24 sm:px-6">
            <h1 className="text-center text-2xl font-bold text-white">Crear cuenta</h1>

            {site?.auth?.google_enabled && (
                <div className="mt-8 space-y-4">
                    <GoogleButton label="Registrarme con Google" />
                    <div className="flex items-center gap-3 text-xs uppercase text-white/50">
                        <span className="h-px flex-1 bg-white/10" /> o con tu correo <span className="h-px flex-1 bg-white/10" />
                    </div>
                </div>
            )}

            <form onSubmit={handleSubmit} className="mt-6 space-y-4">
                <div className="contact-form-field">
                    <label className="text-sm text-white/70">Nombre completo</label>
                    <input required className="contact-input" value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} />
                    {errors.name && <p className="text-xs text-red-400">{errors.name[0]}</p>}
                </div>
                <div className="contact-form-field">
                    <label className="text-sm text-white/70">Correo electrónico</label>
                    <input required type="email" className="contact-input" value={form.email} onChange={(e) => setForm({ ...form, email: e.target.value })} />
                    {errors.email && <p className="text-xs text-red-400">{errors.email[0]}</p>}
                </div>
                <div className="contact-form-field">
                    <label className="text-sm text-white/70">Contraseña</label>
                    <input required type="password" minLength={8} className="contact-input" value={form.password} onChange={(e) => setForm({ ...form, password: e.target.value })} />
                    {errors.password && <p className="text-xs text-red-400">{errors.password[0]}</p>}
                </div>

                {error && <p className="text-sm text-red-400">{error}</p>}

                <button type="submit" disabled={isSubmitting} className="btn-primary w-full">
                    {isSubmitting ? 'Creando cuenta…' : 'Regístrate gratis'}
                </button>
            </form>

            <p className="mt-6 text-center text-sm text-white/60">
                ¿Ya tienes cuenta? <Link to="/ingresar" className="text-brand">Ingresa aquí</Link>
            </p>
        </div>
    );
}
