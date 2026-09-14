import { useEffect, useMemo, useState } from 'react';
import { api } from './api';
import { useCart } from './cart';

export function productIdsMatch(a, b) {
    return String(a) === String(b);
}

export function useCartCatalog() {
    const { items } = useCart();
    const [products, setProducts] = useState(null);

    useEffect(() => {
        if (items.length === 0) {
            setProducts([]);

            return undefined;
        }

        const ids = [...new Set(items.map((item) => String(item.productId)))].join(',');
        let active = true;

        api.get(`/products?ids=${encodeURIComponent(ids)}`, { auth: false })
            .then((payload) => {
                if (active) {
                    setProducts(Array.isArray(payload?.data) ? payload.data : []);
                }
            })
            .catch(() => {
                if (active) {
                    setProducts([]);
                }
            });

        return () => {
            active = false;
        };
    }, [items]);

    const lines = useMemo(() => {
        if (products === null) {
            return null;
        }

        return items.map((item) => {
            const product = products.find((candidate) => productIdsMatch(candidate.id, item.productId));

            return { ...item, product: product ?? null };
        });
    }, [items, products]);

    return {
        lines,
        isLoading: lines === null,
    };
}
