import { useEffect, useRef, useState } from 'react';

/**
 * Envuelve una sección y le agrega un fade+slide sutil la primera vez que
 * entra al viewport. Si el navegador no soporta IntersectionObserver, o el
 * usuario pidió "reducir movimiento", queda visible de inmediato.
 */
export default function Reveal({ as: Tag = 'div', className = '', children }) {
    const ref = useRef(null);
    const [visible, setVisible] = useState(false);

    useEffect(() => {
        if (typeof IntersectionObserver === 'undefined' || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            setVisible(true);

            return;
        }

        const node = ref.current;

        if (!node) {
            return;
        }

        const observer = new IntersectionObserver(
            ([entry]) => {
                if (entry.isIntersecting) {
                    setVisible(true);
                    observer.disconnect();
                }
            },
            { threshold: 0, rootMargin: '0px 0px -60px 0px' },
        );

        observer.observe(node);

        return () => observer.disconnect();
    }, []);

    return (
        <Tag ref={ref} className={`reveal ${visible ? 'is-visible' : ''} ${className}`}>
            {children}
        </Tag>
    );
}
