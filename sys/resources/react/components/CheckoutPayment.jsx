import { useEffect, useState } from 'react';
import { Payment, initMercadoPago } from '@mercadopago/sdk-react';
import { ApiError } from '../lib/api';
import YapePaymentForm from './YapePaymentForm';

export default function CheckoutPayment({ order, buyerEmail, onProcess, error, onError }) {
    const [mpReady, setMpReady] = useState(false);
    const [paymentMethod, setPaymentMethod] = useState(
        String(order?.currency || '').toUpperCase() === 'PEN' ? 'yape' : 'card',
    );

    useEffect(() => {
        if (order?.public_key && !mpReady) {
            initMercadoPago(order.public_key, { locale: 'es-PE' });
            setMpReady(true);
        }
    }, [order, mpReady]);

    const yapeAvailable = String(order?.currency || '').toUpperCase() === 'PEN';

    return (
        <div>
            <p className="mb-4 text-sm text-white/60">Elige cómo pagar. Todo el cobro ocurre aquí, sin salir del sitio.</p>
            {yapeAvailable && (
                <div className="mb-6 flex flex-wrap gap-2">
                    <button
                        type="button"
                        className={paymentMethod === 'yape' ? 'btn-primary' : 'btn-secondary'}
                        onClick={() => { setPaymentMethod('yape'); onError(''); }}
                    >
                        Yape
                    </button>
                    <button
                        type="button"
                        className={paymentMethod === 'card' ? 'btn-primary' : 'btn-secondary'}
                        onClick={() => { setPaymentMethod('card'); onError(''); }}
                    >
                        Tarjeta / Mercado Pago
                    </button>
                </div>
            )}
            {yapeAvailable && paymentMethod === 'yape' ? (
                <YapePaymentForm
                    order={order}
                    buyerEmail={buyerEmail || order.buyer_email}
                    onProcess={onProcess}
                />
            ) : (
                <>
                    {!yapeAvailable && (
                        <p className="mb-6 text-sm text-white/70">
                            Tarjetas y billetera Mercado Pago.
                        </p>
                    )}
                    {mpReady && (
                        <Payment
                            initialization={{ amount: order.total, preferenceId: order.preference_id }}
                            customization={{ paymentMethods: { creditCard: 'all', debitCard: 'all', mercadoPago: 'all' } }}
                            onSubmit={async ({ formData }) => {
                                try {
                                    await onProcess(formData);
                                } catch (processError) {
                                    onError(processError instanceof ApiError ? processError.message : 'No pudimos procesar el pago.');
                                }
                            }}
                            onError={() => onError('El medio de pago no pudo cargar. Intenta de nuevo.')}
                        />
                    )}
                </>
            )}
            {error && <p className="mt-4 text-sm text-red-400">{error}</p>}
        </div>
    );
}
