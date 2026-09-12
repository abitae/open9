import { useEffect, useState } from 'react';
import { useRoutes } from 'react-router-dom';
import Preloader from './components/Preloader';
import { AuthProvider } from './lib/auth';
import { CartProvider } from './lib/cart';
import { SiteProvider, useSite } from './lib/site';
import { routes } from './routes';

const MIN_PRELOADER_MS = 900;

function AppRoutes() {
    return useRoutes(routes);
}

function AppShell() {
    const { isLoading } = useSite();
    const [minTimeElapsed, setMinTimeElapsed] = useState(false);

    useEffect(() => {
        const timeout = setTimeout(() => setMinTimeElapsed(true), MIN_PRELOADER_MS);

        return () => clearTimeout(timeout);
    }, []);

    const showPreloader = isLoading || !minTimeElapsed;

    return (
        <>
            <Preloader visible={showPreloader} />
            <AuthProvider>
                <CartProvider>
                    <AppRoutes />
                </CartProvider>
            </AuthProvider>
        </>
    );
}

export default function App() {
    return (
        <SiteProvider>
            <AppShell />
        </SiteProvider>
    );
}
