import { useState } from 'react';
import { Link, useNavigate, useSearchParams } from 'react-router-dom';
import { ApiError } from '../lib/api';
import { useAuth } from '../lib/auth';

export default function ResetPasswordPage() {
    const { resetPassword } = useAuth();
    const navigate = useNavigate();
    const [searchParams] = useSearchParams();
    const emailFromLink = searchParams.get('email') ?? '';
    const token = searchParams.get('token') ?? '';
    const [form, setForm] = useState({
        email: emailFromLink,
        password: '',
        password_confirmation: '',
    });
    const [error, setError] = useState(token ? '' : 'El enlace no es válido o ha expirado.');
    const [errors, setErrors] = useState({});
    const [isSubmitting, setIsSubmitting] = useState(false);

    const handleSubmit = async (event) => {
        event.preventDefault();
        setError('');
        setErrors({});
        setIsSubmitting(true);

        try {
            await resetPassword({
                email: form.email,
                token,
                password: form.password,
                password_confirmation: form.password_confirmation,
            });
            navigate('/ingresar', { replace: true, state: { passwordReset: true } });
        } catch (submitError) {
            if (submitError instanceof ApiError) {
                setError(submitError.message);
                setErrors(submitError.errors ?? {});
            } else {
                setError('No pudimos actualizar la contraseña.');
            }
        } finally {
            setIsSubmitting(false);
        }
    };

    return (
        <div className="mx-auto max-w-md px-4 py-24 sm:px-6">
            <h1 className="text-center text-2xl font-bold text-white">Nueva contraseña</h1>
            <p className="mt-3 text-center text-sm text-white/60">
                Elige una contraseña de al menos 8 caracteres.
            </p>

            <form onSubmit={handleSubmit} className="mt-8 space-y-4">
                <div className="contact-form-field">
                    <label className="text-sm text-white/70">Correo electrónico</label>
                    <input
                        required
                        type="email"
                        className="contact-input"
                        value={form.email}
                        onChange={(e) => setForm({ ...form, email: e.target.value })}
                    />
                    {errors.email && <p className="text-xs text-red-400">{errors.email[0]}</p>}
                </div>
                <div className="contact-form-field">
                    <label className="text-sm text-white/70">Nueva contraseña</label>
                    <input
                        required
                        type="password"
                        minLength={8}
                        className="contact-input"
                        value={form.password}
                        onChange={(e) => setForm({ ...form, password: e.target.value })}
                    />
                    {errors.password && <p className="text-xs text-red-400">{errors.password[0]}</p>}
                </div>
                <div className="contact-form-field">
                    <label className="text-sm text-white/70">Confirmar contraseña</label>
                    <input
                        required
                        type="password"
                        minLength={8}
                        className="contact-input"
                        value={form.password_confirmation}
                        onChange={(e) => setForm({ ...form, password_confirmation: e.target.value })}
                    />
                </div>

                {error && <p className="text-sm text-red-400">{error}</p>}

                <button type="submit" disabled={isSubmitting || !token} className="btn-primary w-full">
                    {isSubmitting ? 'Guardando…' : 'Guardar contraseña'}
                </button>
            </form>

            <p className="mt-6 text-center text-sm text-white/60">
                <Link to="/recuperar-contraseña" className="text-brand">Pedir un enlace nuevo</Link>
            </p>
        </div>
    );
}
