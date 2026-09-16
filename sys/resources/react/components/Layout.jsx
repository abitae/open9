import { Outlet, useLocation } from 'react-router-dom';
import ChatWidget from './ChatWidget';
import CookieBanner from './CookieBanner';
import Footer from './Footer';
import Nav from './Nav';
import RocketField from './RocketField';
import ScrollToTop from './ScrollToTop';

export default function Layout() {
    const location = useLocation();

    return (
        <div className="relative isolate flex min-h-screen flex-col bg-open9-black">
            <ScrollToTop />
            <RocketField />
            <a href="#contenido" className="skip-link">Saltar al contenido</a>
            <Nav />
            <main id="contenido" className="relative z-10 flex-1">
                <div key={location.pathname} className="page-fade">
                    <Outlet />
                </div>
            </main>
            <Footer />
            <CookieBanner />
            <ChatWidget />
        </div>
    );
}
