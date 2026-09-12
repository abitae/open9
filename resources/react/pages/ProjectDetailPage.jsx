import { useEffect, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import { api, ApiError } from '../lib/api';
import SafeImage from '../components/SafeImage';

export default function ProjectDetailPage() {
    const { slug } = useParams();
    const [project, setProject] = useState(null);
    const [notFound, setNotFound] = useState(false);

    useEffect(() => {
        let active = true;
        setNotFound(false);
        setProject(null);

        api.get(`/projects/${slug}`, { auth: false })
            .then((data) => active && setProject(data))
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
                <p className="text-lg text-white">No encontramos este proyecto.</p>
                <Link to="/proyectos" className="btn-secondary mt-6 inline-flex">Volver a proyectos</Link>
            </div>
        );
    }

    if (!project) {
        return <div className="mx-auto max-w-4xl px-4 py-24 sm:px-6"><div className="h-64 animate-pulse rounded-2xl bg-white/5" /></div>;
    }

    return (
        <article className="mx-auto max-w-4xl px-4 py-16 sm:px-6">
            <p className="text-xs uppercase tracking-wide text-brand">{project.category} · {project.year}</p>
            <h1 className="mt-2 text-3xl font-bold text-white sm:text-4xl">{project.title}</h1>
            <SafeImage src={project.image_url} alt={project.title} className="mt-8 w-full rounded-2xl object-cover" />
            <p className="mt-8 text-white/70">{project.description_full ?? project.description}</p>

            {(project.tags ?? []).length > 0 && (
                <div className="mt-6 flex flex-wrap gap-2">
                    {project.tags.map((tag) => (
                        <span key={tag} className="filter-pill">{tag}</span>
                    ))}
                </div>
            )}

            {(project.gallery ?? []).length > 0 && (
                <div className="mt-10 grid gap-4 sm:grid-cols-2">
                    {project.gallery.map((url) => (
                        <SafeImage key={url} src={url} alt={project.title} className="rounded-xl object-cover" />
                    ))}
                </div>
            )}

            <Link to="/proyectos" className="btn-ghost mt-10 inline-flex">← Volver a proyectos</Link>
        </article>
    );
}
