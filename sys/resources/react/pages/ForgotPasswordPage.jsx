import { useState } from 'react';
import { Link } from 'react-router-dom';
import { ApiError } from '../lib/api';
import { useAuth } from '../lib/auth';

export default function ForgotPasswordPage() {
    const { forgotPassword } = useAuth();
    const [email, setEmail] = useState('');
    const [error, setError] = useState('');
    const [status, setStatus] = useState('');
    const [isSubmitting, setIsSubmitting] = useState(false);

    const handleSubmit = async (event) => {
        event.preventDefault();
        setError('');
        setStatus('');
        setIsSubmitting(true);

        try {
            const payload = await forgotPassword(email);
            setStatus(payload?.message ?? 'Si el correo está registrado, te enviamos instrucciones.');
        } catch (submitError) {
            setError(submitError instanceof ApiError ? submitError.message : 'No pudimos enviar las instrucciones.');
        } finally {
            setIsSubmitting(false);
        }
    };

    return (
        <div className="mx-auto max-w-md px-4 py-24 sm:px-6">
            <h1 className="text-center text-2xl font-bold text-white">Recuperar contraseña</h1>
            <p className="mt-3 text-center text-sm text-white/60">
                Te enviaremos un enlace para elegir una nueva contraseña.
            </p>

            <form onSubmit={handleSubmit} className="mt-8 space-y-4">
                <div className="contact-form-field">
                    <label className="text-sm text-white/70">Correo electrónico</label>
                    <input
                        required
                        type="email"
                        className="contact-input"
                        value={email}
                        onChange={(e) => setEmail(e.target.value)}
                    />
                </div>

                {status && <p className="text-sm text-white/70">{status}</p>}
                {error && <p className="text-sm text-red-400">{error}</p>}

                <button type="submit" disabled={isSubmitting} className="btn-primary w-full">
                    {isSubmitting ? 'Enviando…' : 'Enviar instrucciones'}
                </button>
            </form>

            <p className="mt-6 text-center text-sm text-white/60">
                ¿La recordaste? <Link to="/ingresar" className="text-brand">Ingresa aquí</Link>
            </p>
        </div>
    );
}
