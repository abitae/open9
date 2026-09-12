import { useEffect, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import { api, ApiError } from '../lib/api';

export default function LegalPage() {
    const { slug } = useParams();
    const [page, setPage] = useState(null);
    const [notFound, setNotFound] = useState(false);

    useEffect(() => {
        let active = true;
        setPage(null);
        setNotFound(false);

        api.get(`/legal/${slug}`, { auth: false })
            .then((data) => active && setPage(data))
            .catch((error) => {
                if (active && error instanceof ApiError && error.status === 404) {
                    setNotFound(true);
                }
            });

        return () => {
            active = false;
        };
    }, [slug]);

    if (notFound) {
        return (
            <div className="mx-auto max-w-2xl px-4 py-24 text-center sm:px-6">
                <p className="text-lg text-white">Esta página legal no está disponible.</p>
                <Link to="/" className="btn-secondary mt-6 inline-flex">Volver al inicio</Link>
            </div>
        );
    }

    if (!page) {
        return <div className="mx-auto max-w-3xl px-4 py-24 sm:px-6"><div className="h-64 animate-pulse rounded-2xl bg-white/5" /></div>;
    }

    return (
        <article className="mx-auto max-w-3xl px-4 py-16 sm:px-6">
            <h1 className="text-3xl font-bold text-white">{page.title}</h1>
            <div className="prose prose-invert mt-8 max-w-none text-white/80">
                {(page.blocks ?? []).map((block, index) => (
                    block.type === 'heading'
                        ? <h2 key={index} className="mt-8 text-xl font-semibold text-white">{block.content}</h2>
                        : <p key={index} className="mt-4">{block.content}</p>
                ))}
            </div>
        </article>
    );
}
