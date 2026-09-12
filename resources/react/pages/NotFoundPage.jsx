import { Link } from 'react-router-dom';

export default function NotFoundPage() {
    return (
        <div className="mx-auto max-w-2xl px-4 py-32 text-center sm:px-6">
            <p className="text-sm uppercase tracking-wide text-brand">Error 404</p>
            <h1 className="mt-4 text-3xl font-bold text-white">No encontramos esta página</h1>
            <Link to="/" className="btn-primary mt-8 inline-flex">Volver al inicio</Link>
        </div>
    );
}
