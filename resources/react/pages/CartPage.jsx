import { useEffect, useMemo, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { Trash2 } from 'lucide-react';
import PageHeader from '../components/PageHeader';
import QuantityStepper from '../components/QuantityStepper';
import SafeImage from '../components/SafeImage';
import { api } from '../lib/api';
import { useCart } from '../lib/cart';
import { explodeRocket } from '../lib/cartFx';
import { formatMoney } from '../lib/format';

export default function CartPage() {
    const { items, updateQuantity, removeItem } = useCart();
    const [products, setProducts] = useState(null);
    const navigate = useNavigate();

    useEffect(() => {
        api.get('/products', { auth: false }).then(({ data }) => setProducts(data));
    }, []);

    const lines = useMemo(() => {
        if (!products) {
            return [];
        }

        return items
            .map((item) => {
                const product = products.find((candidate) => candidate.id === item.productId);

                return product ? { ...item, product } : null;
            })
            .filter(Boolean);
    }, [items, products]);

    const total = lines.reduce((sum, line) => sum + line.product.price * line.quantity, 0);
    const currency = lines[0]?.product.currency ?? 'USD';

    if (items.length === 0) {
        return (
            <div className="mx-auto max-w-2xl px-4 py-24 text-center sm:px-6">
                <h1 className="text-2xl font-bold text-white">Tu carrito está vacío</h1>
                <Link to="/tienda" className="btn-primary mt-6 inline-flex">Explorar catálogo</Link>
            </div>
        );
    }

    return (
        <div>
            <PageHeader eyebrow="Carrito" title="Revisa tu pedido" />

            <div className="mx-auto max-w-4xl px-4 pb-20 sm:px-6">
                {!products ? (
                    <div className="h-40 animate-pulse rounded-2xl bg-white/5" />
                ) : (
                    <div className="space-y-4">
                        {lines.map((line) => (
                            <div key={line.productId} className="flex items-center gap-4 rounded-2xl border border-white/10 bg-white/5 p-4">
                                {line.product.image_url && (
                                    <SafeImage src={line.product.image_url} alt={line.product.name} className="size-20 rounded-xl object-cover" />
                                )}
                                <div className="flex-1">
                                    <p className="font-semibold text-white">{line.product.name}</p>
                                    <p className="text-sm text-white/50">{formatMoney(line.product.price, line.product.currency)}</p>
                                </div>
                                <QuantityStepper
                                    value={line.quantity}
                                    onChange={(quantity) => updateQuantity(line.productId, quantity)}
                                    max={typeof line.product.stock === 'number' ? line.product.stock : null}
                                />
                                <button
                                    type="button"
                                    onClick={(event) => {
                                        explodeRocket(event.currentTarget);
                                        removeItem(line.productId);
                                    }}
                                    aria-label={`Quitar ${line.product.name} del carrito`}
                                    className="btn-ghost !px-2 text-white/50 hover:text-red-400"
                                >
                                    <Trash2 className="size-4" />
                                </button>
                            </div>
                        ))}

                        <div className="flex items-center justify-between border-t border-white/10 pt-6">
                            <p className="text-lg font-semibold text-white">Total</p>
                            <p className="text-2xl font-bold text-white">{formatMoney(total, currency)}</p>
                        </div>

                        <button type="button" onClick={() => navigate('/checkout')} className="btn-primary w-full">
                            Ir a pagar
                        </button>
                    </div>
                )}
            </div>
        </div>
    );
}
