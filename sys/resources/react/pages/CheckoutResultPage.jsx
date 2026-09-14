import { useEffect, useState } from 'react';
import { Link, useSearchParams } from 'react-router-dom';
import { api, ApiError } from '../lib/api';
import { formatMoney } from '../lib/format';

const STATUS_LABELS = {
    approved: 'Pago aprobado',
    paid: 'Pago aprobado',
    pending: 'Pago pendiente',
    rejected: 'Pago rechazado',
    cancelled: 'Pedido cancelado',
};

export default function CheckoutResultPage() {
    const [searchParams] = useSearchParams();
    const orderCode = searchParams.get('order');
    const [order, setOrder] = useState(null);
    const [notFound, setNotFound] = useState(false);

    useEffect(() => {
        if (!orderCode) {
            setNotFound(true);

            return;
        }

        api.get(`/orders/${orderCode}`, { auth: false })
            .then(setOrder)
            .catch((error) => {
                if (error instanceof ApiError && error.status === 404) {
                    setNotFound(true);
                }
            });
    }, [orderCode]);

    if (notFound) {
        return (
            <div className="mx-auto max-w-2xl px-4 py-24 text-center sm:px-6">
                <p className="text-lg text-white">No encontramos este pedido.</p>
                <Link to="/tienda" className="btn-secondary mt-6 inline-flex">Volver a la tienda</Link>
            </div>
        );
    }

    if (!order) {
        return <div className="mx-auto max-w-2xl px-4 py-24 sm:px-6"><div className="h-40 animate-pulse rounded-2xl bg-white/5" /></div>;
    }

    return (
        <div className="mx-auto max-w-2xl px-4 py-24 text-center sm:px-6">
            <h1 className="text-2xl font-bold text-white">
                {STATUS_LABELS[order.payment_status] ?? STATUS_LABELS[order.status] ?? 'Estado del pedido'}
            </h1>
            <p className="mt-2 text-white/60">Código de pedido: {order.order_code}</p>
            <p className="mt-6 text-3xl font-bold text-white">{formatMoney(order.total, order.currency)}</p>

            <div className="mt-8 space-y-2 text-left">
                {(order.items ?? []).map((item, index) => (
                    <div key={index} className="flex justify-between text-sm text-white/70">
                        <span>{item.quantity} × {item.product_name}</span>
                        <span>{formatMoney(item.subtotal, order.currency)}</span>
                    </div>
                ))}
            </div>

            <Link to="/tienda" className="btn-primary mt-10 inline-flex">Seguir comprando</Link>
        </div>
    );
}
