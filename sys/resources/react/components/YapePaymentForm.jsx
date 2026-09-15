import { useState } from 'react';
import { formatMoney } from '../lib/format';
import { createYapeToken } from '../lib/yapeToken';

export default function YapePaymentForm({ order, buyerEmail, onProcess }) {
    const [phone, setPhone] = useState('');
    const [otp, setOtp] = useState('');
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [error, setError] = useState('');

    const handleSubmit = async (event) => {
        event.preventDefault();
        setError('');

        const phoneNumber = phone.replace(/\D/g, '');
        const code = otp.replace(/\D/g, '');

        if (phoneNumber.length < 9) {
            setError('Ingresa un celular Yape válido.');

            return;
        }

        if (code.length !== 6) {
            setError('El código de Yape debe tener 6 dígitos.');

            return;
        }

        setIsSubmitting(true);

        try {
            const token = await createYapeToken({
                publicKey: order.public_key,
                phoneNumber,
                otp: code,
            });

            await onProcess({
                token,
                payment_method_id: 'yape',
                installments: 1,
                payer: { email: buyerEmail },
            });
        } catch (submitError) {
            setError(submitError instanceof Error ? submitError.message : 'No pudimos procesar el pago con Yape.');
        } finally {
            setIsSubmitting(false);
        }
    };

    return (
        <form onSubmit={handleSubmit} className="space-y-4">
            <p className="text-sm text-white/60">
                Abre Yape, copia el código de 6 dígitos y págalo aquí. El límite lo define tu app (S/500, S/900 o S/2000).
            </p>
            <div className="contact-form-field">
                <label className="text-sm text-white/70" htmlFor="yape-phone">Celular Yape</label>
                <input
                    id="yape-phone"
                    required
                    inputMode="numeric"
                    autoComplete="tel"
                    className="contact-input"
                    placeholder="987654321"
                    value={phone}
                    onChange={(e) => setPhone(e.target.value.replace(/\D/g, '').slice(0, 11))}
                />
            </div>
            <div className="contact-form-field">
                <label className="text-sm text-white/70" htmlFor="yape-otp">Código de aprobación</label>
                <input
                    id="yape-otp"
                    required
                    inputMode="numeric"
                    pattern="\d{6}"
                    maxLength={6}
                    className="contact-input tracking-[0.4em] text-center text-lg"
                    value={otp}
                    onChange={(e) => setOtp(e.target.value.replace(/\D/g, '').slice(0, 6))}
                />
            </div>
            {error && <p className="text-sm text-red-400">{error}</p>}
            <button type="submit" disabled={isSubmitting || otp.length !== 6} className="btn-primary w-full">
                {isSubmitting ? 'Procesando…' : `Pagar ${formatMoney(order.total, order.currency)}`}
            </button>
        </form>
    );
}
