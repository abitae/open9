import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import PageHeader from '../components/PageHeader';
import LoadingGrid from '../components/LoadingGrid';
import Reveal from '../components/Reveal';
import SafeImage from '../components/SafeImage';
import { api } from '../lib/api';

export default function ServicesPage() {
    const [services, setServices] = useState(null);

    useEffect(() => {
        let active = true;
        api.get('/services', { auth: false }).then(({ data }) => active && setServices(data));

        return () => {
            active = false;
        };
    }, []);

    return (
        <div>
            <PageHeader
                eyebrow="Servicios"
                title="Soluciones de automatización a la medida de tu negocio"
                description="Cada servicio se adapta al alcance y prioridades que definas con nuestro equipo."
            />

            <div className="mx-auto max-w-6xl px-4 pb-20 sm:px-6">
                {!services ? (
                    <LoadingGrid />
                ) : services.length === 0 ? (
                    <p className="text-center text-white/60">Aún no hay servicios publicados.</p>
                ) : (
                    <Reveal className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        {services.map((service) => (
                            <div key={service.slug} className="card-hover flex flex-col rounded-2xl border border-white/10 bg-white/5 p-6">
                                {service.image_url && (
                                    <SafeImage src={service.image_url} alt={service.title} className="mb-4 h-40 w-full rounded-xl object-cover" />
                                )}
                                <h2 className="text-lg font-semibold text-white">{service.title}</h2>
                                <p className="mt-2 flex-1 text-sm text-white/60">{service.description}</p>
                                {(service.features ?? []).length > 0 && (
                                    <ul className="mt-4 space-y-1 text-sm text-white/70">
                                        {service.features.map((feature) => (
                                            <li key={feature}>• {feature}</li>
                                        ))}
                                    </ul>
                                )}
                                {service.price && (
                                    <p className="mt-4 text-xl font-bold text-white">{service.price}</p>
                                )}
                                <Link to="/contacto" className="btn-secondary mt-6">Contacta ventas</Link>
                            </div>
                        ))}
                    </Reveal>
                )}
            </div>
        </div>
    );
}
