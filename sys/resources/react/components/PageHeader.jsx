export default function PageHeader({ eyebrow, title, description }) {
    return (
        <div className="relative">
            <div className="relative mx-auto max-w-6xl px-4 py-16 text-center sm:px-6">
                {eyebrow && <span className="badge-tech badge-tech-live mb-4">{eyebrow}</span>}
                <h1 className="hero-headline">{title}</h1>
                {description && <p className="mx-auto mt-4 max-w-2xl text-white/60">{description}</p>}
            </div>
        </div>
    );
}
