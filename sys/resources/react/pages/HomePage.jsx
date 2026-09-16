import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import SectionHeader from '../components/SectionHeader';
import LoadingGrid from '../components/LoadingGrid';
import Icon from '../components/Icon';
import Reveal from '../components/Reveal';
import SafeImage from '../components/SafeImage';
import { api } from '../lib/api';

function HeroMedia({ mediaType, imageUrl, videoUrl, alt }) {
    if (mediaType === 'video' && videoUrl) {
        return (
            <video
                src={videoUrl}
                className="absolute inset-0 size-full object-cover"
                autoPlay
                muted
                loop
                playsInline
            />
        );
    }

    if (mediaType === 'image' && imageUrl) {
        return <SafeImage src={imageUrl} alt={alt} className="absolute inset-0 size-full object-cover" />;
    }

    return null;
}

function ShowcaseCard({ card }) {
    if (card.layout === 'featured') {
        return (
            <div className="card-hover relative min-h-[220px] overflow-hidden rounded-2xl border border-white/10">
                <HeroMedia mediaType={card.media_type} imageUrl={card.image_url} videoUrl={card.video_url} alt={card.title} />
                <div className="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-transparent" />
                <div className="relative flex h-full flex-col justify-end p-6">
                    <Icon name={card.icon} className="mb-2 size-6 text-tech-accent" />
                    <h3 className="text-lg font-semibold text-white">{card.title}</h3>
                    {card.description && <p className="mt-1 text-sm text-white/70">{card.description}</p>}
                </div>
            </div>
        );
    }

    return (
        <div className="card-hover flex items-start gap-4 rounded-2xl border border-white/10 bg-white/5 p-5">
            {card.media_type === 'image' && card.image_url ? (
                <SafeImage src={card.image_url} alt={card.title} className="size-14 shrink-0 rounded-xl object-cover" />
            ) : (
                <span className="icon-tile size-11 shrink-0">
                    <Icon name={card.icon} className="size-5" />
                </span>
            )}
            <div>
                <h3 className="font-semibold text-white">{card.title}</h3>
                {card.description && <p className="mt-1 text-sm text-white/60">{card.description}</p>}
            </div>
        </div>
    );
}

function FeatureCard({ card }) {
    return (
        <div className="card-hover rounded-2xl border border-white/10 bg-white/5 p-6">
            <span className="icon-tile mb-4 size-11">
                <Icon name={card.icon} className="size-5" />
            </span>
            <h3 className="text-lg font-semibold text-white">{card.title}</h3>
            <p className="mt-2 text-sm text-white/60">{card.description}</p>
        </div>
    );
}

export default function HomePage() {
    const [home, setHome] = useState(null);
    const [featuredProjects, setFeaturedProjects] = useState(null);
    const [isLoading, setIsLoading] = useState(true);

    useEffect(() => {
        let active = true;

        api.get('/home', { auth: false })
            .then((data) => active && setHome(data))
            .finally(() => active && setIsLoading(false));

        api.get('/projects?per_page=50', { auth: false })
            .then(({ data }) => {
                if (!active) {
                    return;
                }

                const featured = data.filter((project) => project.featured);
                setFeaturedProjects((featured.length > 0 ? featured : data).slice(0, 3));
            });

        return () => {
            active = false;
        };
    }, []);

    if (isLoading) {
        return (
            <div className="mx-auto max-w-6xl px-4 py-16 sm:px-6">
                <LoadingGrid count={3} />
            </div>
        );
    }

    if (!home) {
        return (
            <div className="mx-auto max-w-6xl px-4 py-24 text-center sm:px-6">
                <p className="text-white/60">No pudimos cargar el contenido de la portada.</p>
            </div>
        );
    }

    const {
        hero_panel: hero,
        hero_showcase: showcase,
        section_headers: sections,
        stats,
        feature_cards: features,
        workflow_steps: steps,
        quick_links: quickLinks,
        pricing_plans: plans,
        testimonials,
    } = home;

    const serviceCards = (features ?? []).filter((card) => card.card_type === 'service');
    const solutionCards = (features ?? []).filter((card) => card.card_type === 'solution');

    return (
        <div>
            <section className="mx-auto max-w-6xl px-4 py-12 sm:px-6">
                <div className="hero-enter grid gap-6 lg:grid-cols-[1.3fr_1fr]">
                    <div className="relative overflow-hidden rounded-3xl border border-white/10 p-8 sm:p-14">
                        <HeroMedia mediaType={hero?.media_type} imageUrl={hero?.image_url} videoUrl={hero?.video_url} alt={hero?.headline?.pre} />
                        {(hero?.media_type === 'image' || hero?.media_type === 'video') ? (
                            <div className="absolute inset-0 bg-open9-black/90" />
                        ) : (
                            <div className="hero-orb -left-24 -top-24 size-72" />
                        )}

                        <div className="relative">
                            {hero?.badge_label && <span className="badge-tech badge-tech-live mb-6">{hero.badge_label}</span>}
                            <h1 className="hero-headline leading-[1.15]">
                                {hero?.headline?.pre} <span className="text-brand">{hero?.headline?.highlight}</span>
                                {hero?.headline?.subtitle && (
                                    <>
                                        <br />
                                        {hero.headline.subtitle} <span className="text-brand">{hero.headline.subtitle_highlight}</span>
                                    </>
                                )}
                            </h1>
                            {hero?.description && <p className="mt-6 max-w-xl text-lg text-white/70">{hero.description}</p>}

                            {hero?.pills?.length > 0 && (
                                <p className="mt-5 text-sm text-white/50">
                                    {hero.pills.join('  ·  ')}
                                </p>
                            )}

                            {hero?.cta?.url && (
                                <Link to={hero.cta.url} className="btn-primary mt-8 inline-flex">
                                    {hero.cta.icon && <Icon name={hero.cta.icon} className="size-4" />}
                                    {hero.cta.label}
                                </Link>
                            )}

                            {hero?.stats?.length > 0 && (
                                <div className="hero-stats mt-12 max-w-md">
                                    {hero.stats.map((stat) => (
                                        <div key={stat.label}>
                                            <p className="text-3xl font-bold text-white">{stat.value}{stat.suffix}</p>
                                            <p className="mt-1 text-sm text-white/60">{stat.label}</p>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </div>
                    </div>

                    <div className="flex flex-col gap-6">
                        {hero?.quote?.primary && (
                            <div className="terminal-card">
                                <div className="terminal-card-bar">
                                    <span className="terminal-dot bg-[#ff5f57]" />
                                    <span className="terminal-dot bg-[#febc2e]" />
                                    <span className="terminal-dot bg-[#28c840]" />
                                    <span className="ml-2 text-xs text-white/40">open9.sh</span>
                                </div>
                                <div className="p-6 font-mono">
                                    {hero.quote.kicker && (
                                        <p className="text-xs text-tech-accent">
                                            <span className="text-white/30">$</span> {hero.quote.kicker}
                                        </p>
                                    )}
                                    <p className="mt-3 text-lg font-semibold leading-relaxed text-white">
                                        <span className="text-brand">&gt;</span> {hero.quote.primary}
                                        <br />
                                        <span className="pl-4">{hero.quote.secondary}</span>
                                        <span className="terminal-cursor" aria-hidden="true" />
                                    </p>
                                    {hero.quote.footer && <p className="mt-4 text-xs text-white/40"># {hero.quote.footer}</p>}
                                </div>
                            </div>
                        )}

                        {(showcase?.cards ?? []).map((card, index) => (
                            <ShowcaseCard key={index} card={card} />
                        ))}
                    </div>
                </div>
            </section>

            {stats?.length > 0 && (
                <Reveal as="section" className="mx-auto max-w-6xl px-4 py-16 sm:px-6">
                    <SectionHeader
                        eyebrow={sections?.stats?.label}
                        title={sections?.stats?.title ?? 'Resultados'}
                        description={sections?.stats?.description}
                        align="center"
                    />
                    <div className="grid grid-cols-2 gap-6 sm:grid-cols-4">
                        {stats.map((stat) => (
                            <div key={stat.title} className="rounded-2xl border border-white/10 bg-white/5 p-6 text-center">
                                <span className="icon-tile mx-auto mb-3 size-11">
                                    <Icon name={stat.icon} className="size-5" />
                                </span>
                                <p className="text-3xl font-bold text-white">{stat.value}{stat.suffix}</p>
                                <p className="mt-1 text-sm text-white/60">{stat.title}</p>
                            </div>
                        ))}
                    </div>
                </Reveal>
            )}

            {serviceCards.length > 0 && (
                <Reveal as="section" className="mx-auto max-w-6xl px-4 py-16 sm:px-6">
                    <SectionHeader
                        eyebrow={sections?.platform_services?.label}
                        title={[sections?.platform_services?.title, sections?.platform_services?.title_highlight].filter(Boolean).join(' ') || 'Qué hacemos'}
                        description={sections?.platform_services?.description}
                        align="center"
                    />
                    <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        {serviceCards.map((card) => <FeatureCard key={card.title} card={card} />)}
                    </div>
                </Reveal>
            )}

            {solutionCards.length > 0 && (
                <Reveal as="section" className="mx-auto max-w-6xl px-4 py-16 sm:px-6">
                    <SectionHeader
                        eyebrow={sections?.platform_solutions?.label}
                        title={[sections?.platform_solutions?.title, sections?.platform_solutions?.title_highlight].filter(Boolean).join(' ') || 'Soluciones por rubro'}
                        description={sections?.platform_solutions?.description}
                        align="center"
                    />
                    <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        {solutionCards.map((card) => <FeatureCard key={card.title} card={card} />)}
                    </div>
                </Reveal>
            )}

            {steps?.length > 0 && (
                <Reveal as="section" className="mx-auto max-w-6xl px-4 py-16 sm:px-6">
                    <SectionHeader
                        eyebrow={sections?.workflow?.label}
                        title={[sections?.workflow?.title, sections?.workflow?.title_highlight].filter(Boolean).join(' ') || 'Cómo trabajamos'}
                        description={sections?.workflow?.description}
                        align="center"
                    />
                    <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                        {steps.map((step) => (
                            <div key={step.step_number} className="rounded-2xl border border-white/10 p-6">
                                <div className="flex items-center gap-3">
                                    <span className="icon-tile size-10">
                                        <Icon name={step.icon} className="size-5" />
                                    </span>
                                    <span className="text-sm font-semibold text-white/50">Paso {step.step_number}</span>
                                </div>
                                <h3 className="mt-3 text-lg font-semibold text-white">{step.title}</h3>
                                <p className="mt-2 text-sm text-white/60">{step.description}</p>
                            </div>
                        ))}
                    </div>
                    {sections?.workflow?.cta_url && (
                        <div className="mt-8 text-center">
                            <Link to={sections.workflow.cta_url} className="btn-secondary inline-flex">
                                {sections.workflow.cta_label}
                            </Link>
                        </div>
                    )}
                </Reveal>
            )}

            {featuredProjects?.length > 0 && (
                <Reveal as="section" className="mx-auto max-w-6xl px-4 py-16 sm:px-6">
                    <SectionHeader
                        eyebrow={sections?.projects_preview?.label}
                        title={[sections?.projects_preview?.title, sections?.projects_preview?.title_highlight].filter(Boolean).join(' ') || 'Casos reales'}
                        description={sections?.projects_preview?.description}
                        align="center"
                    />
                    <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        {featuredProjects.map((project) => (
                            <Link
                                key={project.slug}
                                to={`/proyectos/${project.slug}`}
                                className="card-hover block overflow-hidden rounded-2xl border border-white/10 bg-white/5"
                            >
                                <SafeImage src={project.image_url} alt={project.title} className="h-40 w-full object-cover" />
                                <div className="p-5">
                                    <p className="text-xs uppercase tracking-wide text-brand">{project.category}</p>
                                    <h3 className="mt-1 font-semibold text-white">{project.title}</h3>
                                </div>
                            </Link>
                        ))}
                    </div>
                    <div className="mt-8 text-center">
                        <Link to="/proyectos" className="btn-ghost inline-flex">Ver todos los proyectos →</Link>
                    </div>
                </Reveal>
            )}

            {quickLinks?.length > 0 && (
                <Reveal as="section" className="mx-auto max-w-6xl px-4 py-16 sm:px-6">
                    <SectionHeader
                        eyebrow={sections?.quick_links?.label}
                        title={[sections?.quick_links?.title, sections?.quick_links?.title_highlight].filter(Boolean).join(' ') || 'Explorar'}
                        description={sections?.quick_links?.description}
                        align="center"
                    />
                    <div className="grid gap-4 sm:grid-cols-3">
                        {quickLinks.map((link) => (
                            <Link
                                key={link.title}
                                to={link.link_url}
                                className="card-hover flex items-start gap-3 rounded-2xl border border-white/10 bg-white/5 p-5"
                            >
                                <span className="icon-tile size-10 shrink-0">
                                    <Icon name={link.icon} className="size-5" />
                                </span>
                                <div>
                                    <h3 className="font-semibold text-white">{link.title}</h3>
                                    {link.description && <p className="mt-1 text-sm text-white/60">{link.description}</p>}
                                </div>
                            </Link>
                        ))}
                    </div>
                </Reveal>
            )}

            {plans?.length > 0 && (
                <Reveal as="section" className="mx-auto max-w-6xl px-4 py-16 sm:px-6">
                    <SectionHeader
                        eyebrow={sections?.pricing?.label}
                        title={[sections?.pricing?.title, sections?.pricing?.title_highlight].filter(Boolean).join(' ') || 'Planes'}
                        description={sections?.pricing?.description}
                        align="center"
                    />
                    <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        {plans.map((plan) => (
                            <div
                                key={plan.name}
                                className={`card-hover rounded-2xl border p-6 ${plan.is_highlighted ? 'border-brand bg-brand/10' : 'border-white/10 bg-white/5'}`}
                            >
                                <h3 className="text-lg font-semibold text-white">{plan.name}</h3>
                                <p className="mt-2 text-3xl font-bold text-white">
                                    {plan.price}
                                    {plan.period && <span className="text-sm font-normal text-white/50">/{plan.period}</span>}
                                </p>
                                <p className="mt-2 text-sm text-white/60">{plan.description}</p>
                                <ul className="mt-4 space-y-2 text-sm text-white/70">
                                    {(plan.features ?? []).map((feature) => (
                                        <li key={feature}>• {feature}</li>
                                    ))}
                                </ul>
                                {plan.cta_url && (
                                    <Link to={plan.cta_url} className="btn-primary mt-6 w-full">
                                        {plan.cta_text}
                                    </Link>
                                )}
                            </div>
                        ))}
                    </div>
                </Reveal>
            )}

            {testimonials?.length > 0 && (
                <Reveal as="section" className="mx-auto max-w-6xl px-4 py-16 sm:px-6">
                    <SectionHeader
                        eyebrow={sections?.testimonials?.label}
                        title={[sections?.testimonials?.title, sections?.testimonials?.title_highlight].filter(Boolean).join(' ') || 'Lo que dicen nuestros clientes'}
                        description={sections?.testimonials?.description}
                        align="center"
                    />
                    <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        {testimonials.map((testimonial, index) => (
                            <blockquote key={index} className="rounded-2xl border border-white/10 bg-white/5 p-6">
                                <p className="text-sm text-white/80">“{testimonial.quote}”</p>
                                <footer className="mt-4 text-sm font-semibold text-white">
                                    {testimonial.author}
                                    {testimonial.role && <span className="block font-normal text-white/50">{testimonial.role}</span>}
                                </footer>
                            </blockquote>
                        ))}
                    </div>
                </Reveal>
            )}

            {sections?.cta_contact && (
                <Reveal as="section" className="mx-auto max-w-6xl px-4 py-20 sm:px-6">
                    <div className="relative overflow-hidden rounded-3xl border border-brand/30 bg-brand/10 p-10 text-center sm:p-16">
                        {sections.cta_contact.label && <span className="badge-tech mb-4">{sections.cta_contact.label}</span>}
                        <h2 className="text-2xl font-bold text-white sm:text-3xl">
                            {[sections.cta_contact.title, sections.cta_contact.title_highlight].filter(Boolean).join(' ')}
                        </h2>
                        {sections.cta_contact.description && (
                            <p className="mx-auto mt-3 max-w-xl text-white/70">{sections.cta_contact.description}</p>
                        )}
                        <Link to="/contacto" className="btn-primary mt-8 inline-flex">
                            {sections.cta_contact.cta_label ?? 'Hablemos'}
                        </Link>
                    </div>
                </Reveal>
            )}
        </div>
    );
}
