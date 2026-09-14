import { useEffect, useState } from 'react';
import { Link, useNavigate, useSearchParams } from 'react-router-dom';
import { useAuth } from '../lib/auth';
import { GOOGLE_AUTH_ERROR_MESSAGES } from '../lib/googleAuthErrors';

export default function AuthCallbackPage() {
    const [searchParams] = useSearchParams();
    const { loginWithToken } = useAuth();
    const navigate = useNavigate();
    const [error, setError] = useState(null);

    useEffect(() => {
        const token = searchParams.get('token');
        const errorCode = searchParams.get('error');

        if (errorCode) {
            setError(GOOGLE_AUTH_ERROR_MESSAGES[errorCode] ?? 'No pudimos completar el acceso con Google.');

            return;
        }

        if (token) {
            loginWithToken(token).then(() => navigate('/cuenta', { replace: true }));

            return;
        }

        setError('No pudimos completar el acceso con Google.');
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    if (error) {
        return (
            <div className="mx-auto max-w-md px-4 py-24 text-center sm:px-6">
                <p className="text-white">{error}</p>
                <Link to="/ingresar" className="btn-secondary mt-6 inline-flex">Volver a ingresar</Link>
            </div>
        );
    }

    return (
        <div className="mx-auto max-w-md px-4 py-24 text-center sm:px-6">
            <p className="text-white/60">Completando tu acceso…</p>
        </div>
    );
}
