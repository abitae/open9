export default function SectionHeader({ eyebrow, title, description, align = 'left' }) {
    return (
        <div className={`mb-8 ${align === 'center' ? 'text-center' : ''}`}>
            {eyebrow && <span className="badge-tech mb-3">{eyebrow}</span>}
            <h2 className="text-2xl font-bold text-white sm:text-3xl">{title}</h2>
            {description && <p className="mt-2 max-w-2xl text-white/60">{description}</p>}
        </div>
    );
}
