import { useEffect, useRef, useState } from 'react';
import { Link, useSearchParams } from 'react-router-dom';
import PageHeader from '../components/PageHeader';
import LoadingGrid from '../components/LoadingGrid';
import Reveal from '../components/Reveal';
import SearchInput from '../components/SearchInput';
import Pagination from '../components/Pagination';
import SafeImage from '../components/SafeImage';
import { api } from '../lib/api';
import { useDebouncedValue } from '../lib/useDebouncedValue';
import { formatDate } from '../lib/format';

export default function BlogPage() {
    const [searchParams, setSearchParams] = useSearchParams();
    const category = searchParams.get('category') ?? '';
    const [searchInput, setSearchInput] = useState('');
    const search = useDebouncedValue(searchInput, 350);
    const [page, setPage] = useState(1);

    const [result, setResult] = useState(null);
    const [categories, setCategories] = useState([]);
    const topRef = useRef(null);

    useEffect(() => {
        api.get('/blog-categories', { auth: false }).then(({ data }) => setCategories(data));
    }, []);

    useEffect(() => {
        setPage(1);
    }, [category, search]);

    useEffect(() => {
        let active = true;
        setResult(null);

        const params = new URLSearchParams();
        if (category) params.set('category', category);
        if (search) params.set('search', search);
        if (page > 1) params.set('page', String(page));

        api.get(`/blog?${params.toString()}`, { auth: false }).then((data) => active && setResult(data));

        return () => {
            active = false;
        };
    }, [category, search, page]);

    const goToPage = (nextPage) => {
        setPage(nextPage);
        topRef.current?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    };

    const selectCategory = (slug) => {
        if (slug) {
            setSearchParams({ category: slug });
        } else {
            setSearchParams({});
        }
    };

    const posts = result?.data;
    const meta = result?.meta;

    return (
        <div>
            <div ref={topRef} />
            <PageHeader eyebrow="Blog" title="Ideas sobre automatización, IA y tecnología" />

            <div className="mx-auto max-w-6xl px-4 pb-20 sm:px-6">
                <div className="mb-6">
                    <SearchInput value={searchInput} onChange={setSearchInput} placeholder="Buscar artículos…" />
                </div>

                {categories.length > 0 && (
                    <div className="mb-8 flex flex-wrap gap-2">
                        <button type="button" onClick={() => selectCategory('')} className={`filter-pill ${category === '' ? 'filter-pill-active' : ''}`}>
                            Todas
                        </button>
                        {categories.map((item) => (
                            <button
                                key={item.slug}
                                type="button"
                                onClick={() => selectCategory(item.slug)}
                                className={`filter-pill ${category === item.slug ? 'filter-pill-active' : ''}`}
                            >
                                {item.name}
                            </button>
                        ))}
                    </div>
                )}

                {!posts ? (
                    <LoadingGrid />
                ) : posts.length === 0 ? (
                    <p className="text-center text-white/60">Aún no hay artículos publicados.</p>
                ) : (
                    <>
                        <Reveal className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            {posts.map((post) => (
                                <Link
                                    key={post.slug}
                                    to={`/blog/${post.slug}`}
                                    className="card-hover block overflow-hidden rounded-2xl border border-white/10 bg-white/5"
                                >
                                    {post.image_url && (
                                        <SafeImage src={post.image_url} alt={post.title} className="h-44 w-full object-cover" />
                                    )}
                                    <div className="p-6">
                                        <p className="text-xs uppercase tracking-wide text-brand">{post.category}</p>
                                        <h2 className="mt-2 text-lg font-semibold text-white">{post.title}</h2>
                                        <p className="mt-2 text-sm text-white/60">{post.excerpt}</p>
                                        <p className="mt-4 text-xs text-white/50">
                                            {formatDate(post.date)} · {post.author} · {post.readTime}
                                        </p>
                                    </div>
                                </Link>
                            ))}
                        </Reveal>

                        {meta && <Pagination currentPage={meta.current_page} lastPage={meta.last_page} onChange={goToPage} />}
                    </>
                )}
            </div>
        </div>
    );
}
