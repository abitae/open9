const AUTH_REDIRECT_KEY = 'open9_auth_redirect';

export function rememberAuthRedirect(path) {
    if (typeof path !== 'string' || !path.startsWith('/') || path.startsWith('//')) {
        return;
    }

    try {
        sessionStorage.setItem(AUTH_REDIRECT_KEY, path);
    } catch {
        // El almacenamiento puede no estar disponible (modo privado).
    }
}

export function consumeAuthRedirect(fallback = '/cuenta') {
    try {
        const next = sessionStorage.getItem(AUTH_REDIRECT_KEY);
        sessionStorage.removeItem(AUTH_REDIRECT_KEY);

        if (typeof next === 'string' && next.startsWith('/') && !next.startsWith('//')) {
            return next;
        }
    } catch {
        // El almacenamiento puede no estar disponible (modo privado).
    }

    return fallback;
}

export default function GoogleButton({ label = 'Continuar con Google', redirectTo }) {
    const handleClick = () => {
        if (redirectTo) {
            rememberAuthRedirect(redirectTo);
        }

        const returnTo = encodeURIComponent(window.location.origin);
        window.location.href = `/api/auth/google/redirect?return_to=${returnTo}`;
    };

    return (
        <button type="button" onClick={handleClick} className="btn-secondary w-full">
            {label}
        </button>
    );
}
