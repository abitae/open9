import { ChevronLeft, ChevronRight } from 'lucide-react';

function pageList(current, last) {
    const pages = new Set([1, last, current, current - 1, current + 1]);

    return [...pages]
        .filter((page) => page >= 1 && page <= last)
        .sort((a, b) => a - b);
}

export default function Pagination({ currentPage, lastPage, onChange }) {
    if (lastPage <= 1) {
        return null;
    }

    const pages = pageList(currentPage, lastPage);

    return (
        <nav aria-label="Paginación" className="mt-10 flex items-center justify-center gap-1">
            <button
                type="button"
                onClick={() => onChange(currentPage - 1)}
                disabled={currentPage <= 1}
                aria-label="Página anterior"
                className="btn-ghost !px-2 disabled:opacity-30"
            >
                <ChevronLeft className="size-4" />
            </button>

            {pages.map((page, index) => (
                <span key={page} className="flex items-center gap-1">
                    {index > 0 && pages[index - 1] !== page - 1 && <span className="px-1 text-white/30">…</span>}
                    <button
                        type="button"
                        onClick={() => onChange(page)}
                        aria-current={page === currentPage ? 'page' : undefined}
                        className={`filter-pill !px-3.5 !py-1.5 ${page === currentPage ? 'filter-pill-active' : ''}`}
                    >
                        {page}
                    </button>
                </span>
            ))}

            <button
                type="button"
                onClick={() => onChange(currentPage + 1)}
                disabled={currentPage >= lastPage}
                aria-label="Página siguiente"
                className="btn-ghost !px-2 disabled:opacity-30"
            >
                <ChevronRight className="size-4" />
            </button>
        </nav>
    );
}
