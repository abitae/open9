import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { hasConsent, setConsent, subscribeConsent } from '../lib/cookieConsent';

export default function CookieBanner() {
    const [visible, setVisible] = useState(!hasConsent());

    useEffect(() => subscribeConsent((consent) => setVisible(consent === null)), []);

    if (!visible) {
        return null;
    }

    return (
        <div className="fixed inset-x-0 bottom-0 z-50 border-t border-white/10 bg-open9-black/95 p-4 backdrop-blur sm:p-6">
            <div className="mx-auto flex max-w-6xl flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <p className="text-sm text-white/80">
                    Usamos cookies técnicas para que el sitio funcione (sesión, carrito e inicio de sesión).
                    Las de medición solo se activan si las aceptas.{' '}
                    <Link to="/legal/cookies" className="text-brand underline-offset-2 hover:underline">Política de cookies</Link>
                </p>
                <div className="flex shrink-0 flex-col gap-2 sm:flex-row">
                    <button type="button" className="btn-secondary" onClick={() => setConsent('rejected')}>
                        Solo técnicas
                    </button>
                    <button type="button" className="btn-primary" onClick={() => setConsent('accepted')}>
                        Aceptar
                    </button>
                </div>
            </div>
        </div>
    );
}
