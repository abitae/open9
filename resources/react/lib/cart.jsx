import { createContext, useCallback, useContext, useEffect, useMemo, useState } from 'react';

const STORAGE_KEY = 'open9_cart';
const CartContext = createContext(null);

function readStoredCart() {
    try {
        const raw = localStorage.getItem(STORAGE_KEY);

        return raw ? JSON.parse(raw) : [];
    } catch {
        return [];
    }
}

function writeStoredCart(items) {
    try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(items));
    } catch {
        // Sin almacenamiento disponible, el carrito solo vive en memoria.
    }
}

export function CartProvider({ children }) {
    const [items, setItems] = useState(() => readStoredCart());

    useEffect(() => {
        writeStoredCart(items);
    }, [items]);

    const addItem = useCallback((productId, quantity = 1) => {
        setItems((current) => {
            const existing = current.find((item) => item.productId === productId);

            if (existing) {
                return current.map((item) => (item.productId === productId
                    ? { ...item, quantity: item.quantity + quantity }
                    : item));
            }

            return [...current, { productId, quantity }];
        });
    }, []);

    const updateQuantity = useCallback((productId, quantity) => {
        setItems((current) => {
            if (quantity <= 0) {
                return current.filter((item) => item.productId !== productId);
            }

            return current.map((item) => (item.productId === productId ? { ...item, quantity } : item));
        });
    }, []);

    const removeItem = useCallback((productId) => {
        setItems((current) => current.filter((item) => item.productId !== productId));
    }, []);

    const clear = useCallback(() => setItems([]), []);

    const value = useMemo(() => ({
        items,
        addItem,
        updateQuantity,
        removeItem,
        clear,
        totalQuantity: items.reduce((sum, item) => sum + item.quantity, 0),
    }), [items, addItem, updateQuantity, removeItem, clear]);

    return <CartContext.Provider value={value}>{children}</CartContext.Provider>;
}

export function useCart() {
    const context = useContext(CartContext);

    if (!context) {
        throw new Error('useCart debe usarse dentro de <CartProvider>.');
    }

    return context;
}
