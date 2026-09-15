import { createContext, useCallback, useContext, useEffect, useState } from 'react';
import { api, getToken, onUnauthenticated, setToken } from './api';

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
    const [client, setClient] = useState(null);
    const [isLoading, setIsLoading] = useState(true);

    const loadClient = useCallback(async () => {
        if (!getToken()) {
            setClient(null);
            setIsLoading(false);

            return;
        }

        try {
            const { client: current } = await api.get('/auth/me');
            setClient(current);
        } catch {
            setToken(null);
            setClient(null);
        } finally {
            setIsLoading(false);
        }
    }, []);

    useEffect(() => {
        loadClient();
    }, [loadClient]);

    useEffect(() => onUnauthenticated(() => {
        setClient(null);
    }), []);

    const login = useCallback(async (email, password) => {
        const { token, client: authenticated } = await api.post('/auth/login', { email, password }, { auth: false });
        setToken(token);
        setClient(authenticated);

        return authenticated;
    }, []);

    const register = useCallback(async (data) => api.post('/auth/register', data, { auth: false }), []);

    const verifyEmail = useCallback(async (email, code) => {
        const { token, client: verified } = await api.post('/auth/verify-email', { email, code }, { auth: false });
        setToken(token);
        setClient(verified);

        return verified;
    }, []);

    const resendVerification = useCallback(async (email) => {
        await api.post('/auth/resend-verification', { email }, { auth: false });
    }, []);

    const forgotPassword = useCallback(async (email) => (
        api.post('/auth/forgot-password', { email }, { auth: false })
    ), []);

    const resetPassword = useCallback(async (data) => (
        api.post('/auth/reset-password', data, { auth: false })
    ), []);

    const loginWithToken = useCallback(async (token) => {
        setToken(token);
        await loadClient();

        if (!getToken()) {
            throw new Error('No pudimos restaurar tu sesión.');
        }
    }, [loadClient]);

    const logout = useCallback(async () => {
        try {
            await api.post('/auth/logout');
        } catch {
            // Si el token ya no es válido no hay nada que revocar en el servidor.
        }

        setToken(null);
        setClient(null);
    }, []);

    return (
        <AuthContext.Provider value={{
            client,
            isLoading,
            isAuthenticated: client !== null,
            login,
            register,
            verifyEmail,
            resendVerification,
            forgotPassword,
            resetPassword,
            loginWithToken,
            logout,
        }}>
            {children}
        </AuthContext.Provider>
    );
}

export function useAuth() {
    const context = useContext(AuthContext);

    if (!context) {
        throw new Error('useAuth debe usarse dentro de <AuthProvider>.');
    }

    return context;
}
