import { useEffect, useState } from 'react';
import { Payment, initMercadoPago } from '@mercadopago/sdk-react';
import { Link, useNavigate } from 'react-router-dom';
import GoogleButton from '../components/GoogleButton';
import PageHeader from '../components/PageHeader';
import { api, ApiError } from '../lib/api';
import { useAuth } from '../lib/auth';
import { useCart } from '../lib/cart';
import { useSite } from '../lib/site';
import { formatMoney } from '../lib/format';
import { useCartCatalog } from '../lib/useCartCatalog';

export default function CheckoutPage() {
    const { site } = useSite();
    const { client, isAuthenticated, isLoading: authLoading } = useAuth();
    const { items, clear } = useCart();
    const { lines, isLoading } = useCartCatalog();
    const navigate = useNavigate();

    const [addresses, setAddresses] = useState([]);
    const [selectedAddressId, setSelectedAddressId] = useState('');
    const [buyer, setBuyer] = useState({ name: '', email: '', phone: '', notes: '' });
    const [shipping, setShipping] = useState({ recipient_name: '', phone: '', line1: '', line2: '', city: '', region: '', country: '', postal_code: '' });
    const [order, setOrder] = useState(null);
    const [error, setError] = useState('');
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [mpReady, setMpReady] = useState(false);

    useEffect(() => {
        if (!authLoading && isAuthenticated && client && !client.email_verified) {
            navigate('/verificar-email', { replace: true, state: { email: client.email, from: { pathname: '/checkout' } } });
        }
    }, [authLoading, isAuthenticated, client, navigate]);

    useEffect(() => {
        if (client) {
            setBuyer((current) => ({ ...current, name: client.name ?? '', email: client.email ?? '', phone: client.phone ?? '' }));
        }
    }, [client]);

    useEffect(() => {
        if (isAuthenticated) {
            api.get('/account/addresses')
                .then(({ addresses: list }) => setAddresses(list))
                .catch(() => setAddresses([]));
        }
    }, [isAuthenticated]);

    useEffect(() => {
        if (order?.public_key && !mpReady) {
            initMercadoPago(order.public_key, { locale: 'es-PE' });
            setMpReady(true);
        }
    }, [order, mpReady]);

    const availableLines = (lines ?? []).filter((line) => line.product);
    const total = availableLines.reduce((sum, line) => sum + line.product.price * line.quantity, 0);
    const currency = availableLines[0]?.product.currency ?? 'USD';

    const applySavedAddress = (id) => {
        setSelectedAddressId(id);
        const address = addresses.find((candidate) => String(candidate.id) === id);

        if (address) {
            setShipping({
                recipient_name: address.recipient_name ?? '',
                phone: address.phone ?? '',
                line1: address.line1 ?? '',
                line2: address.line2 ?? '',
                city: address.city ?? '',
                region: address.region ?? '',
                country: address.country ?? '',
                postal_code: address.postal_code ?? '',
            });
        }
    };

    const handleCreateOrder = async (event) => {
        event.preventDefault();
        setError('');
        setIsSubmitting(true);

        if (isLoading || availableLines.length === 0 || availableLines.length !== items.length) {
            setError('Algunos productos del carrito ya no están disponibles.');
            setIsSubmitting(false);

            return;
        }

        try {
            const payload = {
                buyer,
                items: availableLines.map((item) => ({ product_id: Number(item.productId), quantity: item.quantity })),
                shipping_address: shipping.line1 ? shipping : undefined,
            };

            const created = await api.post('/checkout', payload);
            setOrder(created);
        } catch (submitError) {
            if (submitError instanceof ApiError) {
                setError(submitError.message);
            } else {
                setError('No pudimos iniciar tu compra. Intenta de nuevo.');
            }
        } finally {
            setIsSubmitting(false);
        }
    };

    if (items.length === 0 && !order) {
        return (
            <div className="mx-auto max-w-2xl px-4 py-24 text-center sm:px-6">
                <p className="text-lg text-white">Tu carrito está vacío</p>
                <Link to="/tienda" className="btn-primary mt-6 inline-flex">Explorar catálogo</Link>
            </div>
        );
    }

    if (site && site.payments?.enabled === false) {
        return (
            <div className="mx-auto max-w-2xl px-4 py-24 text-center sm:px-6">
                <p className="text-lg text-white">
                    Los pagos en línea no están habilitados en este momento. Contáctanos para coordinar tu compra.
                </p>
                <Link to="/contacto" className="btn-primary mt-6 inline-flex">Contacta ventas</Link>
            </div>
        );
    }

    return (
        <div>
            <PageHeader eyebrow="Checkout" title="Finaliza tu compra" />

            <div className="mx-auto max-w-3xl px-4 pb-24 sm:px-6">
                {!order ? (
                    <form onSubmit={handleCreateOrder} className="space-y-8">
                        {!isAuthenticated && (
                            <section>
                                <h2 className="text-lg font-semibold text-white">Crea tu cuenta o ingresa</h2>
                                <p className="mt-1 text-sm text-white/50">
                                    Con Google rellenamos tus datos y guardamos el pedido en tu cuenta. También puedes continuar como invitado.
                                </p>
                                <div className="mt-4 space-y-4">
                                    <GoogleButton label="Registrarme o ingresar con Google" redirectTo="/checkout" />
                                    <div className="flex items-center gap-3 text-xs uppercase text-white/50">
                                        <span className="h-px flex-1 bg-white/10" /> o como invitado <span className="h-px flex-1 bg-white/10" />
                                    </div>
                                </div>
                            </section>
                        )}

                        <section>
                            <h2 className="text-lg font-semibold text-white">Datos del comprador</h2>
                            <p className="mt-1 text-sm text-white/50">
                                Usaremos estos datos para enviarte la confirmación y coordinar la entrega.
                            </p>
                            <div className="mt-4 grid gap-4 sm:grid-cols-2">
                                <div className="contact-form-field">
                                    <label className="text-sm text-white/70">Nombre completo</label>
                                    <input required className="contact-input" value={buyer.name} onChange={(e) => setBuyer({ ...buyer, name: e.target.value })} />
                                </div>
                                <div className="contact-form-field">
                                    <label className="text-sm text-white/70">Correo electrónico</label>
                                    <input required type="email" className="contact-input" value={buyer.email} onChange={(e) => setBuyer({ ...buyer, email: e.target.value })} />
                                </div>
                            </div>
                        </section>

                        <section>
                            <h2 className="text-lg font-semibold text-white">Dirección de envío</h2>
                            {addresses.length > 0 && (
                                <div className="contact-form-field mt-3">
                                    <label className="text-sm text-white/70">Elige una de tus direcciones guardadas.</label>
                                    <select className="contact-input" value={selectedAddressId} onChange={(e) => applySavedAddress(e.target.value)}>
                                        <option value="">Nueva dirección</option>
                                        {addresses.map((address) => (
                                            <option key={address.id} value={address.id}>
                                                {address.label ?? address.line1}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                            )}
                            <div className="mt-4 grid gap-4 sm:grid-cols-2">
                                <input placeholder="Dirección" className="contact-input" value={shipping.line1} onChange={(e) => setShipping({ ...shipping, line1: e.target.value })} />
                                <input placeholder="Ciudad" className="contact-input" value={shipping.city} onChange={(e) => setShipping({ ...shipping, city: e.target.value })} />
                            </div>
                        </section>

                        <div className="flex items-center justify-between border-t border-white/10 pt-6">
                            <p className="text-lg font-semibold text-white">Total</p>
                            <p className="text-2xl font-bold text-white">{formatMoney(total, currency)}</p>
                        </div>

                        {error && <p className="text-sm text-red-400">{error}</p>}

                        <button type="submit" disabled={isSubmitting || isLoading || availableLines.length === 0} className="btn-primary w-full">
                            {isSubmitting ? 'Procesando…' : 'Continuar al pago'}
                        </button>
                    </form>
                ) : (
                    <div>
                        <p className="mb-4 text-sm text-white/60">Cargando medios de pago seguros...</p>
                        <p className="mb-6 text-sm text-white/70">
                            Tarjetas, Yape y billetera Mercado Pago. Todo el pago ocurre aquí, sin salir del sitio.
                        </p>
                        {mpReady && (
                            <Payment
                                initialization={{ amount: order.total, preferenceId: order.preference_id }}
                                customization={{ paymentMethods: { creditCard: 'all', debitCard: 'all', mercadoPago: 'all' } }}
                                onSubmit={async ({ formData }) => {
                                    try {
                                        const result = await api.post('/checkout/process', { order_code: order.order_code, form_data: formData });
                                        const approved = result.status === 'approved' || result.payment_status === 'paid';
                                        const rejected = result.status === 'rejected' || result.payment_status === 'failed';

                                        if (rejected) {
                                            setError('El pago fue rechazado. Intenta con otro medio de pago.');

                                            return;
                                        }

                                        if (approved) {
                                            clear();
                                        }

                                        navigate(`/checkout/resultado?order=${order.order_code}`);
                                    } catch (processError) {
                                        setError(processError instanceof ApiError ? processError.message : 'No pudimos procesar el pago.');
                                    }
                                }}
                                onError={() => setError('El medio de pago no pudo cargar. Intenta de nuevo.')}
                            />
                        )}
                        {error && <p className="mt-4 text-sm text-red-400">{error}</p>}
                    </div>
                )}
            </div>
        </div>
    );
}
