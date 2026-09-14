import { useEffect, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import { api, ApiError } from '../lib/api';
import SafeImage from '../components/SafeImage';
import { formatDate } from '../lib/format';

export default function BlogDetailPage() {
    const { slug } = useParams();
    const [post, setPost] = useState(null);
    const [notFound, setNotFound] = useState(false);

    useEffect(() => {
        let active = true;
        setNotFound(false);
        setPost(null);

        api.get(`/blog/${slug}`, { auth: false })
            .then((data) => active && setPost(data))
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
                <p className="text-lg text-white">No encontramos este artículo.</p>
                <Link to="/blog" className="btn-secondary mt-6 inline-flex">Volver al blog</Link>
            </div>
        );
    }

    if (!post) {
        return <div className="mx-auto max-w-3xl px-4 py-24 sm:px-6"><div className="h-64 animate-pulse rounded-2xl bg-white/5" /></div>;
    }

    return (
        <article className="mx-auto max-w-3xl px-4 py-16 sm:px-6">
            <p className="text-xs uppercase tracking-wide text-brand">{post.category}</p>
            <h1 className="mt-2 text-3xl font-bold text-white sm:text-4xl">{post.title}</h1>
            <p className="mt-3 text-sm text-white/50">
                {formatDate(post.date)} · {post.author} · {post.readTime}
            </p>

            <SafeImage src={post.image_url} alt={post.title} className="mt-8 w-full rounded-2xl object-cover" />

            <div className="prose prose-invert mt-8 max-w-none text-white/80">
                {(post.content ?? []).map((paragraph, index) => (
                    <p key={index} className="mt-4">{paragraph}</p>
                ))}
            </div>

            {(post.tags ?? []).length > 0 && (
                <div className="mt-8 flex flex-wrap gap-2">
                    {post.tags.map((tag) => (
                        <span key={tag} className="filter-pill">{tag}</span>
                    ))}
                </div>
            )}

            <Link to="/blog" className="btn-ghost mt-10 inline-flex">← Volver al blog</Link>
        </article>
    );
}
