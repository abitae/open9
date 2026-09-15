function loadMercadoPagoCtor() {
    if (typeof window.MercadoPago === 'function') {
        return Promise.resolve(window.MercadoPago);
    }

    return new Promise((resolve, reject) => {
        const existing = document.querySelector('script[src*="sdk.mercadopago.com/js/v2"]');

        const onReady = () => {
            if (typeof window.MercadoPago === 'function') {
                resolve(window.MercadoPago);

                return;
            }

            reject(new Error('Mercado Pago aún no está listo.'));
        };

        if (existing) {
            existing.addEventListener('load', onReady, { once: true });
            existing.addEventListener('error', () => reject(new Error('No se pudo cargar Mercado Pago.')), { once: true });

            if (existing.dataset.loaded === 'true' || existing.readyState === 'complete') {
                onReady();
            }

            return;
        }

        const script = document.createElement('script');
        script.src = 'https://sdk.mercadopago.com/js/v2';
        script.async = true;
        script.onload = () => {
            script.dataset.loaded = 'true';
            onReady();
        };
        script.onerror = () => reject(new Error('No se pudo cargar Mercado Pago.'));
        document.head.appendChild(script);
    });
}

export async function createYapeToken({ publicKey, phoneNumber, otp }) {
    const MercadoPagoCtor = await loadMercadoPagoCtor();
    const mp = new MercadoPagoCtor(publicKey, { locale: 'es-PE' });

    if (typeof mp.yape !== 'function') {
        throw new Error('Este medio de pago no está disponible en esta cuenta de Mercado Pago.');
    }

    const yape = mp.yape({ otp, phoneNumber });
    const token = await yape.create();

    if (typeof token === 'string' && token !== '') {
        return token;
    }

    if (token && typeof token.id === 'string' && token.id !== '') {
        return token.id;
    }

    throw new Error('No se pudo generar el token de Yape.');
}
