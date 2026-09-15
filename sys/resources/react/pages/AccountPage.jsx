import { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import CheckoutPayment from '../components/CheckoutPayment';
import PageHeader from '../components/PageHeader';
import Pagination from '../components/Pagination';
import { api, ApiError } from '../lib/api';
import { useAuth } from '../lib/auth';
import { formatMoney, formatDate } from '../lib/format';
import { useSite } from '../lib/site';

const TABS = [
    { id: 'profile', label: 'Perfil' },
    { id: 'password', label: 'Contraseña' },
    { id: 'addresses', label: 'Direcciones' },
    { id: 'orders', label: 'Pedidos' },
];

const PAYMENT_LABELS = {
    unpaid: 'Sin pagar',
    pending: 'Pago pendiente',
    paid: 'Pagado',
    failed: 'Pago fallido',
};

const ORDER_STATUS_LABELS = {
    pending: 'Pendiente',
    confirmed: 'Confirmado',
    cancelled: 'Cancelado',
    completed: 'Completado',
};

function isUnauthorized(error) {
    return error instanceof ApiError && error.status === 401;
}

function fieldError(error, fallback) {
    if (!(error instanceof ApiError)) {
        return fallback;
    }

    const first = error.errors ? Object.values(error.errors).flat()[0] : null;

    return first || error.message || fallback;
}

export default function AccountPage() {
    const [tab, setTab] = useState('profile');
    const { logout } = useAuth();

    return (
        <div>
            <PageHeader eyebrow="Mi cuenta" title="Gestiona tus datos y pedidos" />

            <div className="mx-auto max-w-4xl px-4 pb-20 sm:px-6">
                <div className="mb-8 flex flex-col gap-4 border-b border-white/10 pb-4 sm:flex-row sm:items-center sm:justify-between">
                    <div className="flex flex-wrap gap-2">
                        {TABS.map((item) => (
                            <button
                                key={item.id}
                                type="button"
                                onClick={() => setTab(item.id)}
                                className={`filter-pill ${tab === item.id ? 'filter-pill-active' : ''}`}
                            >
                                {item.label}
                            </button>
                        ))}
                    </div>
                    <button type="button" onClick={logout} className="btn-ghost self-start sm:self-auto">Cerrar sesión</button>
                </div>

                {tab === 'profile' && <ProfileTab />}
                {tab === 'password' && <PasswordTab />}
                {tab === 'addresses' && <AddressesTab />}
                {tab === 'orders' && <OrdersTab />}
            </div>
        </div>
    );
}

function ProfileTab() {
    const { client } = useAuth();
    const [form, setForm] = useState({
        name: client?.name ?? '',
        email: client?.email ?? '',
        phone: client?.phone ?? '',
    });
    const [status, setStatus] = useState(client ? 'idle' : 'loading');
    const [error, setError] = useState('');

    useEffect(() => {
        let active = true;

        api.get('/account/profile')
            .then(({ client: current }) => {
                if (!active) {
                    return;
                }

                setForm({
                    name: current.name ?? '',
                    email: current.email ?? '',
                    phone: current.phone ?? '',
                });
                setStatus('idle');
            })
            .catch((loadError) => {
                if (!active || isUnauthorized(loadError)) {
                    return;
                }

                setError(loadError instanceof ApiError ? loadError.message : 'No pudimos cargar tu perfil.');
                setStatus('idle');
            });

        return () => {
            active = false;
        };
    }, []);

    const handleSubmit = async (event) => {
        event.preventDefault();
        setError('');
        setStatus('saving');

        try {
            await api.put('/account/profile', form);
            setStatus('saved');
        } catch (submitError) {
            if (isUnauthorized(submitError)) {
                return;
            }

            setError(fieldError(submitError, 'No pudimos guardar tus datos.'));
            setStatus('idle');
        }
    };

    if (status === 'loading') {
        return <div className="h-40 animate-pulse rounded-2xl bg-white/5" />;
    }

    return (
        <form onSubmit={handleSubmit} className="space-y-8">
            <section>
                <h2 className="text-lg font-semibold text-white">Datos personales</h2>
                <p className="mt-1 text-sm text-white/50">Mantén tu información actualizada para tus compras.</p>
                <div className="mt-4 grid gap-4 sm:grid-cols-2">
                    <div className="contact-form-field">
                        <label className="text-sm text-white/70">Nombre completo</label>
                        <input required className="contact-input" value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} />
                    </div>
                    <div className="contact-form-field">
                        <label className="text-sm text-white/70">Correo electrónico</label>
                        <input required type="email" className="contact-input" value={form.email} onChange={(e) => setForm({ ...form, email: e.target.value })} />
                    </div>
                    <div className="contact-form-field">
                        <label className="text-sm text-white/70">Teléfono</label>
                        <input className="contact-input" value={form.phone} onChange={(e) => setForm({ ...form, phone: e.target.value })} />
                    </div>
                </div>
            </section>

            {error && <p className="text-sm text-red-400">{error}</p>}
            {status === 'saved' && <p className="text-sm text-emerald-400">Guardado.</p>}

            <button type="submit" disabled={status === 'saving'} className="btn-primary">
                {status === 'saving' ? 'Guardando…' : 'Guardar cambios'}
            </button>
        </form>
    );
}

function PasswordTab() {
    const { client } = useAuth();
    const [hasPassword, setHasPassword] = useState(client?.has_password ?? true);
    const [form, setForm] = useState({
        current_password: '',
        password: '',
        password_confirmation: '',
    });
    const [status, setStatus] = useState('idle');
    const [error, setError] = useState('');

    useEffect(() => {
        let active = true;

        api.get('/account/profile')
            .then(({ client: current }) => {
                if (active) {
                    setHasPassword(Boolean(current.has_password));
                }
            })
            .catch((loadError) => {
                if (!active || isUnauthorized(loadError)) {
                    return;
                }

                setError(loadError instanceof ApiError ? loadError.message : 'No pudimos cargar tu cuenta.');
            });

        return () => {
            active = false;
        };
    }, []);

    const handleSubmit = async (event) => {
        event.preventDefault();
        setError('');
        setStatus('saving');

        try {
            const payload = {
                password: form.password,
                password_confirmation: form.password_confirmation,
            };

            if (hasPassword) {
                payload.current_password = form.current_password;
            }

            const { client: updated } = await api.put('/account/password', payload);
            setHasPassword(Boolean(updated.has_password));
            setForm({ current_password: '', password: '', password_confirmation: '' });
            setStatus('saved');
        } catch (submitError) {
            if (isUnauthorized(submitError)) {
                return;
            }

            setError(fieldError(submitError, 'No pudimos actualizar la contraseña.'));
            setStatus('idle');
        }
    };

    return (
        <form onSubmit={handleSubmit} className="space-y-8">
            <section>
                <h2 className="text-lg font-semibold text-white">{hasPassword ? 'Cambiar contraseña' : 'Crear contraseña'}</h2>
                <p className="mt-1 text-sm text-white/50">
                    {hasPassword
                        ? 'Usa una contraseña distinta a las anteriores.'
                        : 'Tu cuenta se creó con Google. Puedes definir una contraseña para ingresar también con correo.'}
                </p>
                <div className="mt-4 grid gap-4 sm:grid-cols-2">
                    {hasPassword && (
                        <div className="contact-form-field sm:col-span-2">
                            <label className="text-sm text-white/70">Contraseña actual</label>
                            <input required type="password" className="contact-input" value={form.current_password} onChange={(e) => setForm({ ...form, current_password: e.target.value })} />
                        </div>
                    )}
                    <div className="contact-form-field">
                        <label className="text-sm text-white/70">Nueva contraseña</label>
                        <input required type="password" minLength={8} className="contact-input" value={form.password} onChange={(e) => setForm({ ...form, password: e.target.value })} />
                    </div>
                    <div className="contact-form-field">
                        <label className="text-sm text-white/70">Confirmar nueva contraseña</label>
                        <input required type="password" minLength={8} className="contact-input" value={form.password_confirmation} onChange={(e) => setForm({ ...form, password_confirmation: e.target.value })} />
                    </div>
                </div>
            </section>

            {error && <p className="text-sm text-red-400">{error}</p>}
            {status === 'saved' && <p className="text-sm text-emerald-400">Contraseña actualizada.</p>}

            <button type="submit" disabled={status === 'saving'} className="btn-primary">
                {status === 'saving' ? 'Guardando…' : hasPassword ? 'Cambiar contraseña' : 'Crear contraseña'}
            </button>
        </form>
    );
}

function AddressesTab() {
    const [addresses, setAddresses] = useState(null);
    const [form, setForm] = useState({ recipient_name: '', phone: '', line1: '', line2: '', city: '', region: '', country: '', postal_code: '' });
    const [error, setError] = useState('');

    const load = () => api.get('/account/addresses')
        .then(({ addresses: list }) => setAddresses(list))
        .catch((loadError) => {
            if (isUnauthorized(loadError)) {
                return;
            }

            setAddresses([]);
            setError(loadError instanceof ApiError ? loadError.message : 'No pudimos cargar tus direcciones.');
        });

    useEffect(() => {
        load();
    }, []);

    const handleSubmit = async (event) => {
        event.preventDefault();
        setError('');

        try {
            await api.post('/account/addresses', form);
            setForm({ recipient_name: '', phone: '', line1: '', line2: '', city: '', region: '', country: '', postal_code: '' });
            load();
        } catch (submitError) {
            if (isUnauthorized(submitError)) {
                return;
            }

            setError(submitError instanceof ApiError ? submitError.message : 'No pudimos guardar la dirección.');
        }
    };

    const setDefault = async (id) => {
        try {
            await api.post(`/account/addresses/${id}/default`);
            load();
        } catch (actionError) {
            if (!isUnauthorized(actionError)) {
                setError(actionError instanceof ApiError ? actionError.message : 'No pudimos actualizar la dirección.');
            }
        }
    };

    const remove = async (id) => {
        try {
            await api.delete(`/account/addresses/${id}`);
            load();
        } catch (actionError) {
            if (!isUnauthorized(actionError)) {
                setError(actionError instanceof ApiError ? actionError.message : 'No pudimos eliminar la dirección.');
            }
        }
    };

    return (
        <div className="space-y-8">
            <div>
                <h2 className="text-lg font-semibold text-white">Tus direcciones</h2>
                <p className="mt-1 text-sm text-white/50">Guarda tus direcciones para agilizar el checkout.</p>

                {!addresses ? (
                    <div className="mt-4 h-24 animate-pulse rounded-2xl bg-white/5" />
                ) : addresses.length === 0 ? (
                    <p className="mt-4 text-sm text-white/50">Todavía no tienes direcciones guardadas.</p>
                ) : (
                    <div className="mt-4 space-y-3">
                        {addresses.map((address) => (
                            <div key={address.id} className="flex items-center justify-between rounded-xl border border-white/10 bg-white/5 p-4">
                                <div className="text-sm text-white/80">
                                    <p className="font-semibold text-white">{address.recipient_name}</p>
                                    <p>{address.line1}, {address.city}</p>
                                </div>
                                <div className="flex gap-2">
                                    {!address.is_default && (
                                        <button type="button" onClick={() => setDefault(address.id)} className="btn-ghost">
                                            Marcar predeterminada
                                        </button>
                                    )}
                                    <button type="button" onClick={() => remove(address.id)} className="btn-ghost">Eliminar</button>
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </div>

            <form onSubmit={handleSubmit} className="space-y-4 border-t border-white/10 pt-6">
                <h3 className="text-sm font-semibold text-white">Añadir nueva dirección</h3>
                <div className="grid gap-4 sm:grid-cols-2">
                    <input required placeholder="Nombre del destinatario" className="contact-input" value={form.recipient_name} onChange={(e) => setForm({ ...form, recipient_name: e.target.value })} />
                    <input required placeholder="Dirección" className="contact-input" value={form.line1} onChange={(e) => setForm({ ...form, line1: e.target.value })} />
                    <input required placeholder="Ciudad" className="contact-input" value={form.city} onChange={(e) => setForm({ ...form, city: e.target.value })} />
                    <input placeholder="País" className="contact-input" value={form.country} onChange={(e) => setForm({ ...form, country: e.target.value })} />
                </div>
                {error && <p className="text-sm text-red-400">{error}</p>}
                <button type="submit" className="btn-secondary">Guardar dirección</button>
            </form>
        </div>
    );
}

function OrdersTab() {
    const { site } = useSite();
    const navigate = useNavigate();
    const paymentsEnabled = site?.payments?.enabled !== false;
    const [orders, setOrders] = useState(null);
    const [meta, setMeta] = useState({ current_page: 1, last_page: 1 });
    const [page, setPage] = useState(1);
    const [error, setError] = useState('');
    const [detail, setDetail] = useState(null);
    const [detailStatus, setDetailStatus] = useState('idle');
    const [paying, setPaying] = useState(null);
    const [payError, setPayError] = useState('');
    const [payStatus, setPayStatus] = useState('idle');

    const loadList = (nextPage = 1) => {
        api.get(`/account/orders?page=${nextPage}`)
            .then(({ data, meta: pagination }) => {
                setOrders(data);
                setMeta(pagination ?? { current_page: 1, last_page: 1 });
                setPage(pagination?.current_page ?? nextPage);
            })
            .catch((loadError) => {
                if (isUnauthorized(loadError)) {
                    return;
                }

                setOrders([]);
                setError(loadError instanceof ApiError ? loadError.message : 'No pudimos cargar tus pedidos.');
            });
    };

    useEffect(() => {
        loadList(1);
    }, []);

    const openDetail = async (orderCode) => {
        setDetailStatus('loading');
        setPayError('');
        setPaying(null);

        try {
            const { order } = await api.get(`/account/orders/${orderCode}`);
            setDetail(order);
            setDetailStatus('idle');
        } catch (loadError) {
            if (isUnauthorized(loadError)) {
                return;
            }

            setDetailStatus('idle');
            setError(loadError instanceof ApiError ? loadError.message : 'No pudimos cargar el pedido.');
        }
    };

    const startPay = async (orderCode) => {
        setPayError('');
        setPayStatus('loading');

        try {
            const session = await api.post(`/account/orders/${orderCode}/pay`);
            setPaying(session);
            setPayStatus('idle');
        } catch (actionError) {
            if (isUnauthorized(actionError)) {
                return;
            }

            setPayStatus('idle');
            setPayError(actionError instanceof ApiError ? actionError.message : 'No pudimos iniciar el pago.');
        }
    };

    const processPayment = async (formData) => {
        const result = await api.post('/checkout/process', { order_code: paying.order_code, form_data: formData });
        const approved = result.status === 'approved' || result.payment_status === 'paid';
        const rejected = result.status === 'rejected' || result.payment_status === 'failed';

        if (rejected) {
            setPayError('El pago fue rechazado. Intenta con otro medio de pago.');

            return;
        }

        if (approved || result.payment_status === 'pending') {
            navigate(`/checkout/resultado?order=${paying.order_code}`);
        }
    };

    const backToList = () => {
        setDetail(null);
        setPaying(null);
        setPayError('');
        loadList(page);
    };

    if (paying) {
        return (
            <div className="space-y-6">
                <button type="button" onClick={backToList} className="btn-ghost px-0">← Volver a pedidos</button>
                <div>
                    <h2 className="text-lg font-semibold text-white">Pagar pedido {paying.order_code}</h2>
                    <p className="mt-1 text-sm text-white/50">Total {formatMoney(paying.total, paying.currency)}</p>
                </div>
                <CheckoutPayment
                    order={paying}
                    buyerEmail={paying.buyer_email}
                    onProcess={processPayment}
                    error={payError}
                    onError={setPayError}
                />
            </div>
        );
    }

    if (detailStatus === 'loading') {
        return <div className="h-40 animate-pulse rounded-2xl bg-white/5" />;
    }

    if (detail) {
        const address = detail.shipping_address;

        return (
            <div className="space-y-6">
                <button type="button" onClick={backToList} className="btn-ghost px-0">← Volver a pedidos</button>

                <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 className="text-lg font-semibold text-white">{detail.order_code}</h2>
                        <p className="mt-1 text-sm text-white/50">{formatDate(detail.created_at)}</p>
                    </div>
                    <div className="text-left sm:text-right">
                        <p className="text-xl font-semibold text-white">{formatMoney(detail.total, detail.currency)}</p>
                        <p className="text-sm text-white/50">{PAYMENT_LABELS[detail.payment_status] ?? detail.payment_status}</p>
                        <p className="text-sm text-white/40">{ORDER_STATUS_LABELS[detail.status] ?? detail.status}</p>
                    </div>
                </div>

                <section className="rounded-xl border border-white/10 bg-white/5 p-4">
                    <h3 className="text-sm font-semibold text-white">Artículos</h3>
                    <div className="mt-3 space-y-2">
                        {(detail.items ?? []).map((item, index) => (
                            <div key={`${item.product_name}-${index}`} className="flex justify-between gap-4 text-sm text-white/70">
                                <span>{item.quantity} × {item.product_name}</span>
                                <span className="shrink-0">{formatMoney(item.subtotal, detail.currency)}</span>
                            </div>
                        ))}
                    </div>
                </section>

                {(address || detail.buyer_name) && (
                    <section className="grid gap-4 sm:grid-cols-2">
                        <div className="rounded-xl border border-white/10 bg-white/5 p-4">
                            <h3 className="text-sm font-semibold text-white">Comprador</h3>
                            <p className="mt-2 text-sm text-white/70">{detail.buyer_name}</p>
                            <p className="text-sm text-white/50">{detail.buyer_email}</p>
                            {detail.buyer_phone && <p className="text-sm text-white/50">{detail.buyer_phone}</p>}
                        </div>
                        {address && (
                            <div className="rounded-xl border border-white/10 bg-white/5 p-4">
                                <h3 className="text-sm font-semibold text-white">Envío</h3>
                                <p className="mt-2 text-sm text-white/70">{address.recipient_name || detail.buyer_name}</p>
                                <p className="text-sm text-white/50">{[address.line1, address.line2].filter(Boolean).join(', ')}</p>
                                <p className="text-sm text-white/50">{[address.city, address.region, address.country].filter(Boolean).join(', ')}</p>
                            </div>
                        )}
                    </section>
                )}

                {payError && <p className="text-sm text-red-400">{payError}</p>}

                {detail.can_pay && paymentsEnabled && (
                    <button type="button" disabled={payStatus === 'loading'} onClick={() => startPay(detail.order_code)} className="btn-primary">
                        {payStatus === 'loading' ? 'Preparando pago…' : 'Pagar ahora'}
                    </button>
                )}
            </div>
        );
    }

    if (!orders) {
        return <div className="h-40 animate-pulse rounded-2xl bg-white/5" />;
    }

    if (error) {
        return <p className="text-sm text-red-400">{error}</p>;
    }

    if (orders.length === 0) {
        return <p className="text-sm text-white/50">Aún no tienes pedidos</p>;
    }

    return (
        <div className="space-y-3">
            {payError && <p className="text-sm text-red-400">{payError}</p>}
            {orders.map((order) => (
                <div key={order.order_code} className="flex flex-col gap-4 rounded-xl border border-white/10 bg-white/5 p-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p className="font-semibold text-white">{order.order_code}</p>
                        <p className="text-sm text-white/50">{formatDate(order.created_at)} · {order.items_count} artículos</p>
                    </div>
                    <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-6">
                        <div className="sm:text-right">
                            <p className="font-semibold text-white">{formatMoney(order.total, order.currency)}</p>
                            <p className="text-sm text-white/50">{PAYMENT_LABELS[order.payment_status] ?? order.payment_status}</p>
                        </div>
                        <div className="flex flex-wrap gap-2">
                            <button type="button" onClick={() => openDetail(order.order_code)} className="btn-secondary !px-4 !py-2">
                                Ver detalle
                            </button>
                            {order.can_pay && paymentsEnabled && (
                                <button
                                    type="button"
                                    disabled={payStatus === 'loading'}
                                    onClick={() => startPay(order.order_code)}
                                    className="btn-primary !px-4 !py-2"
                                >
                                    Pagar
                                </button>
                            )}
                        </div>
                    </div>
                </div>
            ))}
            <Pagination currentPage={meta.current_page} lastPage={meta.last_page} onChange={loadList} />
        </div>
    );
}
