import { useEffect, useState } from 'react';
import PageHeader from '../components/PageHeader';
import { api, ApiError } from '../lib/api';
import { useAuth } from '../lib/auth';
import { formatMoney, formatDate } from '../lib/format';

const TABS = [
    { id: 'profile', label: 'Perfil' },
    { id: 'addresses', label: 'Direcciones' },
    { id: 'orders', label: 'Pedidos' },
];

function isUnauthorized(error) {
    return error instanceof ApiError && error.status === 401;
}

export default function AccountPage() {
    const [tab, setTab] = useState('profile');
    const { logout } = useAuth();

    return (
        <div>
            <PageHeader eyebrow="Mi cuenta" title="Gestiona tus datos y pedidos" />

            <div className="mx-auto max-w-4xl px-4 pb-20 sm:px-6">
                <div className="mb-8 flex items-center justify-between border-b border-white/10 pb-4">
                    <div className="flex gap-2">
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
                    <button type="button" onClick={logout} className="btn-ghost">Cerrar sesión</button>
                </div>

                {tab === 'profile' && <ProfileTab />}
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
        current_password: '',
        password: '',
        password_confirmation: '',
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

                setForm((currentForm) => ({
                    ...currentForm,
                    name: current.name ?? '',
                    email: current.email ?? '',
                    phone: current.phone ?? '',
                }));
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
            const payload = { name: form.name, email: form.email, phone: form.phone };

            if (form.password) {
                payload.current_password = form.current_password;
                payload.password = form.password;
                payload.password_confirmation = form.password_confirmation;
            }

            await api.put('/account/profile', payload);
            setForm((current) => ({ ...current, current_password: '', password: '', password_confirmation: '' }));
            setStatus('saved');
        } catch (submitError) {
            if (isUnauthorized(submitError)) {
                return;
            }

            setError(submitError instanceof ApiError ? submitError.message : 'No pudimos guardar tus datos.');
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

            <section>
                <h2 className="text-lg font-semibold text-white">Cambiar contraseña</h2>
                <div className="mt-4 grid gap-4 sm:grid-cols-2">
                    <div className="contact-form-field">
                        <label className="text-sm text-white/70">Contraseña actual</label>
                        <input type="password" className="contact-input" value={form.current_password} onChange={(e) => setForm({ ...form, current_password: e.target.value })} />
                    </div>
                    <div className="contact-form-field">
                        <label className="text-sm text-white/70">Nueva contraseña</label>
                        <input type="password" className="contact-input" value={form.password} onChange={(e) => setForm({ ...form, password: e.target.value })} />
                    </div>
                    <div className="contact-form-field">
                        <label className="text-sm text-white/70">Confirmar nueva contraseña</label>
                        <input type="password" className="contact-input" value={form.password_confirmation} onChange={(e) => setForm({ ...form, password_confirmation: e.target.value })} />
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
    const [orders, setOrders] = useState(null);
    const [error, setError] = useState('');

    useEffect(() => {
        let active = true;

        api.get('/account/orders')
            .then(({ data }) => {
                if (active) {
                    setOrders(data);
                }
            })
            .catch((loadError) => {
                if (!active || isUnauthorized(loadError)) {
                    return;
                }

                setOrders([]);
                setError(loadError instanceof ApiError ? loadError.message : 'No pudimos cargar tus pedidos.');
            });

        return () => {
            active = false;
        };
    }, []);

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
            {orders.map((order) => (
                <div key={order.order_code} className="flex items-center justify-between rounded-xl border border-white/10 bg-white/5 p-4">
                    <div>
                        <p className="font-semibold text-white">{order.order_code}</p>
                        <p className="text-sm text-white/50">{formatDate(order.created_at)} · {order.items_count} artículos</p>
                    </div>
                    <div className="text-right">
                        <p className="font-semibold text-white">{formatMoney(order.total, order.currency)}</p>
                        <p className="text-sm text-white/50">{order.payment_status}</p>
                    </div>
                </div>
            ))}
        </div>
    );
}
