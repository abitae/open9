const TOKEN_KEY = 'open9_client_token';

export function getToken() {
    try {
        return localStorage.getItem(TOKEN_KEY);
    } catch {
        return null;
    }
}

export function setToken(token) {
    try {
        if (token) {
            localStorage.setItem(TOKEN_KEY, token);
        } else {
            localStorage.removeItem(TOKEN_KEY);
        }
    } catch {
        // El almacenamiento puede no estar disponible (modo privado); la sesión
        // simplemente no persistirá entre recargas.
    }
}

export class ApiError extends Error {
    constructor(message, status, errors = null) {
        super(message);
        this.status = status;
        this.errors = errors;
    }
}

async function request(path, { method = 'GET', body, auth = true, headers = {} } = {}) {
    const finalHeaders = {
        Accept: 'application/json',
        ...headers,
    };

    if (body !== undefined) {
        finalHeaders['Content-Type'] = 'application/json';
    }

    if (auth) {
        const token = getToken();

        if (token) {
            finalHeaders.Authorization = `Bearer ${token}`;
        }
    }

    const response = await fetch(`/api${path}`, {
        method,
        headers: finalHeaders,
        body: body !== undefined ? JSON.stringify(body) : undefined,
    });

    let payload = null;

    try {
        payload = await response.json();
    } catch {
        payload = null;
    }

    if (!response.ok) {
        if (response.status === 401) {
            setToken(null);
        }

        throw new ApiError(
            payload?.message ?? 'Ocurrió un error inesperado. Intenta de nuevo.',
            response.status,
            payload?.errors ?? null,
        );
    }

    return payload;
}

export const api = {
    get: (path, options) => request(path, { ...options, method: 'GET' }),
    post: (path, body, options) => request(path, { ...options, method: 'POST', body }),
    put: (path, body, options) => request(path, { ...options, method: 'PUT', body }),
    delete: (path, options) => request(path, { ...options, method: 'DELETE' }),
};
