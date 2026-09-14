import { useState } from 'react';
import { Link, useLocation, useNavigate } from 'react-router-dom';
import { ApiError } from '../lib/api';
import { useAuth } from '../lib/auth';

export default function VerifyEmailPage() {
    const { verifyEmail, resendVerification } = useAuth();
    const navigate = useNavigate();
    const location = useLocation();
    const initialEmail = location.state?.email ?? '';
    const [email, setEmail] = useState(initialEmail);
    const [code, setCode] = useState('');
    const [error, setError] = useState('');
    const [status, setStatus] = useState(initialEmail ? `Te enviamos un código a ${initialEmail}.` : '');
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [isResending, setIsResending] = useState(false);

    const redirectTo = location.state?.from?.pathname ?? '/cuenta';

    const handleSubmit = async (event) => {
        event.preventDefault();
        setError('');
        setIsSubmitting(true);

        try {
            await verifyEmail(email, code);
            navigate(redirectTo, { replace: true });
        } catch (submitError) {
            setError(submitError instanceof ApiError ? submitError.message : 'No pudimos confirmar tu correo.');
        } finally {
            setIsSubmitting(false);
        }
    };

    const handleResend = async () => {
        setError('');
        setStatus('');
        setIsResending(true);

        try {
            await resendVerification(email);
            setStatus('Si el correo está pendiente de confirmación, te enviamos un código.');
        } catch (submitError) {
            setError(submitError instanceof ApiError ? submitError.message : 'No pudimos reenviar el código.');
        } finally {
            setIsResending(false);
        }
    };

    return (
        <div className="mx-auto max-w-md px-4 py-24 sm:px-6">
            <h1 className="text-center text-2xl font-bold text-white">Confirma tu correo</h1>
            <p className="mt-3 text-center text-sm text-white/60">
                Ingresa el código de 6 dígitos que te enviamos para activar tu cuenta.
            </p>

            <form onSubmit={handleSubmit} className="mt-8 space-y-4">
                <div className="contact-form-field">
                    <label className="text-sm text-white/70">Correo electrónico</label>
                    <input required type="email" className="contact-input" value={email} onChange={(e) => setEmail(e.target.value)} />
                </div>
                <div className="contact-form-field">
                    <label className="text-sm text-white/70">Código</label>
                    <input
                        required
                        inputMode="numeric"
                        pattern="\d{6}"
                        maxLength={6}
                        className="contact-input tracking-[0.4em] text-center text-lg"
                        value={code}
                        onChange={(e) => setCode(e.target.value.replace(/\D/g, '').slice(0, 6))}
                    />
                </div>

                {status && <p className="text-sm text-white/70">{status}</p>}
                {error && <p className="text-sm text-red-400">{error}</p>}

                <button type="submit" disabled={isSubmitting || code.length !== 6} className="btn-primary w-full">
                    {isSubmitting ? 'Confirmando…' : 'Confirmar correo'}
                </button>
            </form>

            <button type="button" disabled={isResending || !email} onClick={handleResend} className="btn-secondary mt-4 w-full">
                {isResending ? 'Reenviando…' : 'Reenviar código'}
            </button>

            <p className="mt-6 text-center text-sm text-white/60">
                ¿Ya confirmaste? <Link to="/ingresar" className="text-brand">Ingresa aquí</Link>
            </p>
        </div>
    );
}
