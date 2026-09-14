import { useEffect, useRef, useState } from 'react';
import { Link, useSearchParams } from 'react-router-dom';
import PageHeader from '../components/PageHeader';
import LoadingGrid from '../components/LoadingGrid';
import Reveal from '../components/Reveal';
import SearchInput from '../components/SearchInput';
import Pagination from '../components/Pagination';
import SafeImage from '../components/SafeImage';
import { api } from '../lib/api';
import { useCart } from '../lib/cart';
import { flyRocketToCart } from '../lib/cartFx';
import { useDebouncedValue } from '../lib/useDebouncedValue';
import { formatMoney } from '../lib/format';
import { useSite } from '../lib/site';
import { isOutOfStock } from '../lib/stock';

const SORT_OPTIONS = [
    { value: '', label: 'Ordenar: relevancia' },
    { value: 'price_asc', label: 'Precio: menor a mayor' },
    { value: 'price_desc', label: 'Precio: mayor a menor' },
    { value: 'name', label: 'Nombre A-Z' },
];

export default function StorePage() {
    const [searchParams, setSearchParams] = useSearchParams();
    const brand = searchParams.get('brand') ?? '';
    const category = searchParams.get('category') ?? '';
    const sort = searchParams.get('sort') ?? '';
    const inStock = searchParams.get('in_stock') === '1';
    const [searchInput, setSearchInput] = useState('');
    const search = useDebouncedValue(searchInput, 350);
    const [page, setPage] = useState(1);

    const [result, setResult] = useState(null);
    const [brands, setBrands] = useState([]);
    const [categories, setCategories] = useState([]);
    const { addItem } = useCart();
    const { site } = useSite();
    const topRef = useRef(null);

    useEffect(() => {
        api.get('/product-brands', { auth: false }).then(({ data }) => setBrands(data));
        api.get('/product-categories', { auth: false }).then(({ data }) => setCategories(data));
    }, []);

    useEffect(() => {
        setPage(1);
    }, [brand, category, sort, inStock, search]);

    useEffect(() => {
        let active = true;
        setResult(null);

        const params = new URLSearchParams();
        if (brand) params.set('brand', brand);
        if (category) params.set('category', category);
        if (sort) params.set('sort', sort);
        if (inStock) params.set('in_stock', '1');
        if (search) params.set('search', search);
        if (page > 1) params.set('page', String(page));

        api.get(`/products?${params.toString()}`, { auth: false }).then((data) => active && setResult(data));

        return () => {
            active = false;
        };
    }, [brand, category, sort, inStock, search, page]);

    const goToPage = (nextPage) => {
        setPage(nextPage);
        topRef.current?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    };

    const updateFilter = (key, value) => {
        const params = new URLSearchParams(searchParams);

        if (value) {
            params.set(key, value);
        } else {
            params.delete(key);
        }

        setSearchParams(params);
    };

    const clearFilters = () => {
        setSearchParams({});
        setSearchInput('');
    };

    const activeFilterCount = [brand, category, sort, inStock].filter(Boolean).length;

    const products = result?.data;
    const meta = result?.meta;

    return (
        <div>
            <div ref={topRef} />
            <PageHeader eyebrow="Tienda" title="Hardware y licencias para tu operación" />

            <div className="mx-auto max-w-6xl px-4 pb-20 sm:px-6">
                <div className="mb-6 flex flex-wrap items-center gap-2">
                    <SearchInput value={searchInput} onChange={setSearchInput} placeholder="Buscar productos…" />

                    <select
                        value={category}
                        onChange={(event) => updateFilter('category', event.target.value)}
                        className="contact-input !w-auto !py-2"
                    >
                        <option value="">Categoría: todas</option>
                        {categories.map((item) => (
                            <option key={item.slug} value={item.slug}>{item.name}</option>
                        ))}
                    </select>

                    <select
                        value={brand}
                        onChange={(event) => updateFilter('brand', event.target.value)}
                        className="contact-input !w-auto !py-2"
                    >
                        <option value="">Marca: todas</option>
                        {brands.map((item) => (
                            <option key={item.slug} value={item.slug}>{item.name}</option>
                        ))}
                    </select>

                    <select
                        value={sort}
                        onChange={(event) => updateFilter('sort', event.target.value)}
                        className="contact-input !w-auto !py-2"
                    >
                        {SORT_OPTIONS.map((option) => (
                            <option key={option.value} value={option.value}>{option.label}</option>
                        ))}
                    </select>

                    <label className="flex cursor-pointer items-center gap-2 text-sm text-white/70">
                        <input
                            type="checkbox"
                            checked={inStock}
                            onChange={() => updateFilter('in_stock', inStock ? '' : '1')}
                            className="size-4 rounded border-white/20 bg-white/5 accent-brand"
                        />
                        Disponibles
                    </label>

                    {activeFilterCount > 0 && (
                        <button type="button" onClick={clearFilters} className="btn-ghost !px-2 text-sm text-brand">
                            Limpiar
                        </button>
                    )}
                </div>

                {!products ? (
                    <LoadingGrid />
                ) : products.length === 0 ? (
                    <div className="py-10 text-center text-white/60">
                        <p>No hay productos para este filtro.</p>
                        {activeFilterCount > 0 && (
                            <button type="button" onClick={clearFilters} className="btn-secondary mt-4 inline-flex">
                                Limpiar filtros
                            </button>
                        )}
                    </div>
                ) : (
                    <>
                        {meta && <p className="mb-4 text-sm text-white/50">{meta.total} producto{meta.total === 1 ? '' : 's'} encontrado{meta.total === 1 ? '' : 's'}</p>}

                        <Reveal className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            {products.map((product) => {
                                const outOfStock = isOutOfStock(product, site);

                                return (
                                    <div key={product.slug} className="card-hover flex flex-col rounded-2xl border border-white/10 bg-white/5 p-6">
                                        <Link to={`/tienda/${product.slug}`}>
                                            {product.image_url && (
                                                <SafeImage src={product.image_url} alt={product.name} className="mb-4 h-40 w-full rounded-xl object-cover" />
                                            )}
                                            <p className="text-xs uppercase tracking-wide text-brand">{product.brand}</p>
                                            <h2 className="mt-1 text-lg font-semibold text-white">{product.name}</h2>
                                        </Link>
                                        <p className="mt-2 flex-1 text-sm text-white/60">{product.description}</p>
                                        <p className="mt-4 text-xl font-bold text-white">{formatMoney(product.price, product.currency)}</p>
                                        <button
                                            type="button"
                                            disabled={outOfStock}
                                            onClick={(event) => {
                                                flyRocketToCart(event.currentTarget);
                                                addItem(product.id, 1);
                                            }}
                                            className="btn-primary mt-4"
                                        >
                                            {outOfStock ? 'Agotado' : 'Añadir al carrito'}
                                        </button>
                                    </div>
                                );
                            })}
                        </Reveal>

                        {meta && <Pagination currentPage={meta.current_page} lastPage={meta.last_page} onChange={goToPage} />}
                    </>
                )}
            </div>
        </div>
    );
}
