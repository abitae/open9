import { useEffect, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import { api, ApiError } from '../lib/api';
import SafeImage from '../components/SafeImage';
import { useCart } from '../lib/cart';
import { flyRocketToCart } from '../lib/cartFx';
import { formatMoney } from '../lib/format';
import { useSite } from '../lib/site';
import { isOutOfStock } from '../lib/stock';

export default function ProductDetailPage() {
    const { slug } = useParams();
    const [product, setProduct] = useState(null);
    const [notFound, setNotFound] = useState(false);
    const [activeImage, setActiveImage] = useState(null);
    const { addItem } = useCart();
    const { site } = useSite();

    useEffect(() => {
        let active = true;
        setNotFound(false);
        setProduct(null);
        setActiveImage(null);

        api.get(`/products/${slug}`, { auth: false })
            .then((data) => {
                if (active) {
                    setProduct(data);
                    setActiveImage(data.image_url);
                }
            })
            .catch((error) => {
                if (active && error instanceof ApiError && error.status === 404) {
                    setNotFound(true);
                }
            });

        return () => {
            active = false;
        };
    }, [slug]);

    if (notFound) {
        return (
            <div className="mx-auto max-w-2xl px-4 py-24 text-center sm:px-6">
                <p className="text-lg text-white">No encontramos este producto.</p>
                <Link to="/tienda" className="btn-secondary mt-6 inline-flex">Volver a la tienda</Link>
            </div>
        );
    }

    if (!product) {
        return <div className="mx-auto max-w-4xl px-4 py-24 sm:px-6"><div className="h-64 animate-pulse rounded-2xl bg-white/5" /></div>;
    }

    const outOfStock = isOutOfStock(product, site);

    return (
        <div className="mx-auto grid max-w-5xl gap-10 px-4 py-16 sm:px-6 md:grid-cols-2">
            <div>
                <SafeImage src={activeImage} alt={product.name} className="aspect-square w-full rounded-2xl object-cover" />
                {(product.gallery ?? []).length > 0 && (
                    <div className="mt-4 grid grid-cols-4 gap-2">
                        {[product.image_url, ...product.gallery].filter(Boolean).map((url) => (
                            <button
                                key={url}
                                type="button"
                                onClick={() => setActiveImage(url)}
                                aria-label="Ver esta imagen"
                                aria-current={url === activeImage}
                                className={`overflow-hidden rounded-lg border-2 transition ${url === activeImage ? 'border-brand' : 'border-transparent opacity-70 hover:opacity-100'}`}
                            >
                                <SafeImage src={url} alt={product.name} className="aspect-square w-full object-cover" />
                            </button>
                        ))}
                    </div>
                )}
            </div>

            <div>
                <p className="text-xs uppercase tracking-wide text-brand">{product.brand}</p>
                <h1 className="mt-2 text-3xl font-bold text-white">{product.name}</h1>
                <p className="mt-4 text-2xl font-bold text-white">{formatMoney(product.price, product.currency)}</p>
                <p className="mt-4 text-white/70">{product.description}</p>

                <button
                    type="button"
                    disabled={outOfStock}
                    onClick={(event) => {
                        flyRocketToCart(event.currentTarget);
                        addItem(product.id, 1);
                    }}
                    className="btn-primary mt-8 w-full"
                >
                    {outOfStock ? 'Agotado' : 'Añadir al carrito'}
                </button>

                <Link to="/tienda" className="btn-ghost mt-4 inline-flex">← Volver a la tienda</Link>
            </div>
        </div>
    );
}
