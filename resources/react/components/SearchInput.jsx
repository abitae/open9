import { Search, X } from 'lucide-react';

export default function SearchInput({ value, onChange, placeholder = 'Buscar…' }) {
    return (
        <div className="relative w-full sm:max-w-xs">
            <Search className="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-white/40" />
            <input
                type="search"
                value={value}
                onChange={(event) => onChange(event.target.value)}
                placeholder={placeholder}
                className="contact-input w-full pl-9 pr-9"
            />
            {value && (
                <button
                    type="button"
                    onClick={() => onChange('')}
                    aria-label="Limpiar búsqueda"
                    className="absolute right-2.5 top-1/2 -translate-y-1/2 text-white/40 hover:text-white"
                >
                    <X className="size-4" />
                </button>
            )}
        </div>
    );
}
