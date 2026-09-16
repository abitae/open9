import { useEffect, useState } from 'react';
import { NavLink, useLocation } from 'react-router-dom';
import { ShoppingCart } from 'lucide-react';
import { useAuth } from '../lib/auth';
import { useCart } from '../lib/cart';
import { useSite } from '../lib/site';

const LINKS = [
    { to: '/', label: 'Inicio', end: true },
    { to: '/servicios', label: 'Servicios' },
    { to: '/proyectos', label: 'Proyectos' },
    { to: '/tienda', label: 'Tienda' },
    { to: '/blog', label: 'Blog' },
    { to: '/contacto', label: 'Contacto' },
];

export default function Nav() {
    const { site } = useSite();
    const { isAuthenticated } = useAuth();
    const { totalQuantity } = useCart();
    const [scrolled, setScrolled] = useState(false);
    const [menuOpen, setMenuOpen] = useState(false);
    const location = useLocation();

    useEffect(() => {
        const onScroll = () => setScrolled(window.scrollY > 8);
        onScroll();
        window.addEventListener('scroll', onScroll);

        return () => window.removeEventListener('scroll', onScroll);
    }, []);

    useEffect(() => {
        setMenuOpen(false);
    }, [location.pathname]);

    return (
        <header className={`nav-blur sticky top-0 z-30 ${scrolled ? 'nav-scrolled' : ''}`}>
            <nav className="mx-auto flex max-w-6xl items-center justify-between px-4 py-4 sm:px-6">
                <NavLink to="/" className="flex items-center gap-2">
                    {site?.branding?.logo_dark_url || site?.branding?.logo_url ? (
                        <img
                            src={site.branding.logo_dark_url ?? site.branding.logo_url}
                            alt={site.branding.site_name ?? 'Open9'}
                            className="h-8 w-auto"
                        />
                    ) : (
                        <span className="text-lg font-bold text-white">{site?.branding?.site_name ?? 'OPEN9'}</span>
                    )}
                </NavLink>

                <div className="hidden items-center gap-6 md:flex">
                    {LINKS.map((link) => (
                        <NavLink
                            key={link.to}
                            to={link.to}
                            end={link.end}
                            className={({ isActive }) => `nav-link ${isActive ? 'nav-link-active' : ''}`}
                        >
                            {link.label}
                        </NavLink>
                    ))}
                </div>

                <div className="flex items-center gap-3">
                    <NavLink
                        to="/carrito"
                        data-cart-target
                        aria-label={totalQuantity > 0 ? `Carrito, ${totalQuantity} artículos` : 'Carrito'}
                        className="nav-link relative inline-flex items-center justify-center p-1"
                    >
                        <ShoppingCart className="size-5" strokeWidth={2} />
                        {totalQuantity > 0 && (
                            <span className="absolute -right-1.5 -top-1.5 flex size-5 items-center justify-center rounded-full bg-brand text-[11px] font-semibold text-white">
                                {totalQuantity}
                            </span>
                        )}
                    </NavLink>
                    <NavLink to={isAuthenticated ? '/cuenta' : '/ingresar'} className="btn-secondary !px-4 !py-2 hidden sm:inline-flex">
                        {isAuthenticated ? 'Mi cuenta' : 'Ingresar'}
                    </NavLink>
                    <button
                        type="button"
                        onClick={() => setMenuOpen((open) => !open)}
                        aria-expanded={menuOpen}
                        aria-label={menuOpen ? 'Cerrar menú' : 'Abrir menú'}
                        className="btn-ghost !px-2 md:hidden"
                    >
                        {menuOpen ? '✕' : '☰'}
                    </button>
                </div>
            </nav>

            {menuOpen && (
                <div className="border-t border-white/10 px-4 py-4 md:hidden">
                    <div className="flex flex-col gap-1">
                        {LINKS.map((link) => (
                            <NavLink
                                key={link.to}
                                to={link.to}
                                end={link.end}
                                className={({ isActive }) => `rounded-lg px-3 py-2 nav-link ${isActive ? 'nav-link-active bg-white/5' : ''}`}
                            >
                                {link.label}
                            </NavLink>
                        ))}
                        <NavLink to={isAuthenticated ? '/cuenta' : '/ingresar'} className="rounded-lg px-3 py-2 nav-link sm:hidden">
                            {isAuthenticated ? 'Mi cuenta' : 'Ingresar'}
                        </NavLink>
                    </div>
                </div>
            )}
        </header>
    );
}
