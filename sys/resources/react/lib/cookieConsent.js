const STORAGE_KEY = 'open9_cookie_consent';

const listeners = new Set();

function readStoredConsent() {
    try {
        const raw = localStorage.getItem(STORAGE_KEY);

        if (!raw) {
            return null;
        }

        const parsed = JSON.parse(raw);

        if (parsed?.status === 'accepted' || parsed?.status === 'rejected') {
            return parsed;
        }
    } catch {
        // Sin almacenamiento o JSON inválido: pedimos de nuevo la elección.
    }

    return null;
}

function notify() {
    const consent = readStoredConsent();
    listeners.forEach((listener) => listener(consent));
}

export function getConsent() {
    return readStoredConsent();
}

export function hasConsent() {
    return getConsent() !== null;
}

export function allowNonEssential() {
    return getConsent()?.status === 'accepted';
}

export function setConsent(status) {
    if (status !== 'accepted' && status !== 'rejected') {
        return;
    }

    try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify({
            status,
            at: new Date().toISOString(),
        }));
    } catch {
        // Sin almacenamiento la preferencia no persistirá entre recargas.
    }

    notify();
}

export function resetConsent() {
    try {
        localStorage.removeItem(STORAGE_KEY);
    } catch {
        // El almacenamiento puede no estar disponible.
    }

    notify();
}

export function subscribeConsent(listener) {
    listeners.add(listener);
    listener(readStoredConsent());

    return () => {
        listeners.delete(listener);
    };
}
