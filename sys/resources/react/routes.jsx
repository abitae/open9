import Layout from './components/Layout';
import ProtectedRoute from './components/ProtectedRoute';
import AccountPage from './pages/AccountPage';
import AuthCallbackPage from './pages/AuthCallbackPage';
import BlogDetailPage from './pages/BlogDetailPage';
import BlogPage from './pages/BlogPage';
import CartPage from './pages/CartPage';
import CheckoutPage from './pages/CheckoutPage';
import CheckoutResultPage from './pages/CheckoutResultPage';
import ContactPage from './pages/ContactPage';
import HomePage from './pages/HomePage';
import LegalPage from './pages/LegalPage';
import LoginPage from './pages/LoginPage';
import NotFoundPage from './pages/NotFoundPage';
import ForgotPasswordPage from './pages/ForgotPasswordPage';
import ResetPasswordPage from './pages/ResetPasswordPage';
import ProductDetailPage from './pages/ProductDetailPage';
import ProjectDetailPage from './pages/ProjectDetailPage';
import ProjectsPage from './pages/ProjectsPage';
import RegisterPage from './pages/RegisterPage';
import ServicesPage from './pages/ServicesPage';
import StorePage from './pages/StorePage';
import VerifyEmailPage from './pages/VerifyEmailPage';

export const routes = [
    {
        element: <Layout />,
        children: [
            { path: '/', element: <HomePage /> },
            { path: '/servicios', element: <ServicesPage /> },
            { path: '/proyectos', element: <ProjectsPage /> },
            { path: '/proyectos/:slug', element: <ProjectDetailPage /> },
            { path: '/blog', element: <BlogPage /> },
            { path: '/blog/:slug', element: <BlogDetailPage /> },
            { path: '/contacto', element: <ContactPage /> },
            { path: '/tienda', element: <StorePage /> },
            { path: '/tienda/:slug', element: <ProductDetailPage /> },
            { path: '/carrito', element: <CartPage /> },
            { path: '/checkout', element: <CheckoutPage /> },
            { path: '/checkout/resultado', element: <CheckoutResultPage /> },
            { path: '/ingresar', element: <LoginPage /> },
            { path: '/registro', element: <RegisterPage /> },
            { path: '/verificar-email', element: <VerifyEmailPage /> },
            { path: '/recuperar-contraseña', element: <ForgotPasswordPage /> },
            { path: '/recuperar-contrasena', element: <ForgotPasswordPage /> },
            { path: '/restablecer-contraseña', element: <ResetPasswordPage /> },
            { path: '/restablecer-contrasena', element: <ResetPasswordPage /> },
            { path: '/auth/callback', element: <AuthCallbackPage /> },
            { path: '/cuenta', element: <ProtectedRoute><AccountPage /></ProtectedRoute> },
            { path: '/legal/:slug', element: <LegalPage /> },
            { path: '*', element: <NotFoundPage /> },
        ],
    },
];
