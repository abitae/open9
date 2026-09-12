import { createContext, useContext, useEffect, useState } from 'react';
import { api } from './api';

const SiteContext = createContext(null);

export function SiteProvider({ children }) {
    const [site, setSite] = useState(null);
    const [isLoading, setIsLoading] = useState(true);

    useEffect(() => {
        let active = true;

        api.get('/site', { auth: false })
            .then((data) => {
                if (active) {
                    setSite(data);
                }
            })
            .catch(() => {
                if (active) {
                    setSite(null);
                }
            })
            .finally(() => {
                if (active) {
                    setIsLoading(false);
                }
            });

        return () => {
            active = false;
        };
    }, []);

    return <SiteContext.Provider value={{ site, isLoading }}>{children}</SiteContext.Provider>;
}

export function useSite() {
    const context = useContext(SiteContext);

    if (!context) {
        throw new Error('useSite debe usarse dentro de <SiteProvider>.');
    }

    return context;
}
