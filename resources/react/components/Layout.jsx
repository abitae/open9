import { Outlet, useLocation } from 'react-router-dom';
import ChatWidget from './ChatWidget';
import Footer from './Footer';
import Nav from './Nav';
import ScrollToTop from './ScrollToTop';

export default function Layout() {
    const location = useLocation();

    return (
        <div className="flex min-h-screen flex-col bg-open9-black">
            <ScrollToTop />
            <a href="#contenido" className="skip-link">Saltar al contenido</a>
            <Nav />
            <main id="contenido" className="flex-1">
                <div key={location.pathname} className="page-fade">
                    <Outlet />
                </div>
            </main>
            <Footer />
            <ChatWidget />
        </div>
    );
}
