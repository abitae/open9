export default function AsteroidIcon({ className = 'size-10', style }) {
    return (
        <svg viewBox="0 0 80 80" className={className} style={style} fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path
                d="M22 18 C34 8 54 10 64 22 C74 34 72 52 62 64 C48 74 28 72 18 60 C8 46 10 28 22 18Z"
                fill="#6B7280"
            />
            <path
                d="M26 22 C36 14 52 16 60 26 C54 24 44 22 34 28 C28 32 24 28 26 22Z"
                fill="#D1D5DB"
                opacity="0.45"
            />
            <circle cx="34" cy="36" r="5" fill="#111827" opacity="0.35" />
            <circle cx="50" cy="48" r="4" fill="#111827" opacity="0.28" />
            <circle cx="42" cy="28" r="2.5" fill="#111827" opacity="0.22" />
            <path d="M48 30 C56 34 58 44 54 52" stroke="#9CA3AF" strokeWidth="1.2" opacity="0.5" />
        </svg>
    );
}
