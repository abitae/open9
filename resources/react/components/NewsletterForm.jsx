import { useState } from 'react';
import { api, ApiError } from '../lib/api';

export default function NewsletterForm() {
    const [email, setEmail] = useState('');
    const [status, setStatus] = useState('idle');
    const [error, setError] = useState('');

    const handleSubmit = async (event) => {
        event.preventDefault();
        setStatus('sending');
        setError('');

        try {
            await api.post('/newsletter/subscribe', { email }, { auth: false });
            setStatus('subscribed');
            setEmail('');
        } catch (submitError) {
            setError(submitError instanceof ApiError ? submitError.message : 'No pudimos completar la suscripción.');
            setStatus('idle');
        }
    };

    if (status === 'subscribed') {
        return <p className="text-sm text-emerald-400">Suscripción confirmada.</p>;
    }

    return (
        <form onSubmit={handleSubmit} className="flex flex-col gap-2 sm:flex-row">
            <input
                type="email"
                required
                placeholder="Tu correo"
                value={email}
                onChange={(event) => setEmail(event.target.value)}
                className="contact-input flex-1"
            />
            <button type="submit" disabled={status === 'sending'} className="btn-secondary shrink-0">
                {status === 'sending' ? 'Enviando…' : 'Suscribirme'}
            </button>
            {error && <p className="text-xs text-red-400 sm:basis-full">{error}</p>}
        </form>
    );
}
