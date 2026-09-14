import { Link, useNavigate } from 'react-router-dom';
import { Trash2 } from 'lucide-react';
import PageHeader from '../components/PageHeader';
import QuantityStepper from '../components/QuantityStepper';
import SafeImage from '../components/SafeImage';
import { useCart } from '../lib/cart';
import { explodeRocket } from '../lib/cartFx';
import { formatMoney } from '../lib/format';
import { useCartCatalog } from '../lib/useCartCatalog';
import { useSite } from '../lib/site';
import { stockQuantityMax } from '../lib/stock';

export default function CartPage() {
    const { items, updateQuantity, removeItem } = useCart();
    const { lines, isLoading } = useCartCatalog();
    const { site } = useSite();
    const navigate = useNavigate();

    const availableLines = (lines ?? []).filter((line) => line.product);
    const total = availableLines.reduce((sum, line) => sum + line.product.price * line.quantity, 0);
    const currency = availableLines[0]?.product.currency ?? 'USD';

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
                {isLoading ? (
                    <div className="h-40 animate-pulse rounded-2xl bg-white/5" />
                ) : (
                    <div className="space-y-4">
                        {(lines ?? []).map((line) => (
                            <div key={String(line.productId)} className="flex items-center gap-4 rounded-2xl border border-white/10 bg-white/5 p-4">
                                {line.product?.image_url && (
                                    <SafeImage src={line.product.image_url} alt={line.product.name} className="size-20 rounded-xl object-cover" />
                                )}
                                <div className="flex-1">
                                    {line.product ? (
                                        <>
                                            <p className="font-semibold text-white">{line.product.name}</p>
                                            <p className="text-sm text-white/50">{formatMoney(line.product.price, line.product.currency)}</p>
                                        </>
                                    ) : (
                                        <p className="text-sm text-white/50">Este producto ya no está disponible.</p>
                                    )}
                                </div>
                                {line.product ? (
                                    <QuantityStepper
                                        value={line.quantity}
                                        onChange={(quantity) => updateQuantity(line.productId, quantity)}
                                        max={stockQuantityMax(line.product, site)}
                                    />
                                ) : null}
                                <button
                                    type="button"
                                    onClick={(event) => {
                                        explodeRocket(event.currentTarget);
                                        removeItem(line.productId);
                                    }}
                                    aria-label={`Quitar ${line.product?.name ?? 'producto'} del carrito`}
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

                        <button
                            type="button"
                            disabled={availableLines.length === 0}
                            onClick={() => navigate('/checkout')}
                            className="btn-primary w-full"
                        >
                            Ir a pagar
                        </button>
                    </div>
                )}
            </div>
        </div>
    );
}
