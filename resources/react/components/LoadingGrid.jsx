export default function LoadingGrid({ count = 6 }) {
    return (
        <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            {Array.from({ length: count }).map((_, index) => (
                <div key={index} className="h-64 animate-pulse rounded-2xl bg-white/5" />
            ))}
        </div>
    );
}
