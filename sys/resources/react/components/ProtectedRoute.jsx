import { Navigate, useLocation } from 'react-router-dom';
import { useAuth } from '../lib/auth';

export default function ProtectedRoute({ children }) {
    const { client, isAuthenticated, isLoading } = useAuth();
    const location = useLocation();

    if (isLoading) {
        return null;
    }

    if (!isAuthenticated) {
        return <Navigate to="/ingresar" state={{ from: location }} replace />;
    }

    if (!client?.email_verified) {
        return <Navigate to="/verificar-email" state={{ email: client?.email, from: location }} replace />;
    }

    return children;
}
