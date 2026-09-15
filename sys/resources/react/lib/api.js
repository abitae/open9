const TOKEN_KEY = 'open9_client_token';
const UNAUTHENTICATED_EVENT = 'open9:unauthenticated';

let memoryToken;

function readStoredToken() {
    try {
        return localStorage.getItem(TOKEN_KEY);
    } catch {
        return null;
    }
}

export function getToken() {
    if (memoryToken === undefined) {
        memoryToken = readStoredToken();
    }

    return memoryToken;
}

export function setToken(token) {
    memoryToken = token || null;

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

function notifyUnauthenticated() {
    setToken(null);

    if (typeof window !== 'undefined') {
        window.dispatchEvent(new Event(UNAUTHENTICATED_EVENT));
    }
}

export function onUnauthenticated(listener) {
    if (typeof window === 'undefined') {
        return () => {};
    }

    window.addEventListener(UNAUTHENTICATED_EVENT, listener);

    return () => window.removeEventListener(UNAUTHENTICATED_EVENT, listener);
}

export class ApiError extends Error {
    constructor(message, status, errors = null, payload = null) {
        super(message);
        this.status = status;
        this.errors = errors;
        this.payload = payload;
    }

    get requiresVerification() {
        return Boolean(this.payload?.requires_verification);
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

        if (!token) {
            notifyUnauthenticated();
            throw new ApiError('Unauthenticated.', 401);
        }

        finalHeaders.Authorization = `Bearer ${token}`;
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
        if (auth && response.status === 401) {
            notifyUnauthenticated();
        }

        throw new ApiError(
            payload?.message ?? 'Ocurrió un error inesperado. Intenta de nuevo.',
            response.status,
            payload?.errors ?? null,
            payload,
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
