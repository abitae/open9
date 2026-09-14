import { ArrowUp, Mail, MapPin, Phone } from 'lucide-react';
import { Link } from 'react-router-dom';
import { useSite } from '../lib/site';
import Reveal from './Reveal';
import SocialIcon from './SocialIcon';
import NewsletterForm from './NewsletterForm';

export default function Footer() {
    const { site } = useSite();
    const groups = site?.footer_groups ?? [];

    const scrollToTop = () => window.scrollTo({ top: 0, behavior: 'smooth' });

    return (
        <footer className="mt-24 border-t border-white/10 bg-black/30">
            <Reveal as="div" className="border-b border-white/10 bg-brand/10">
                <div className="mx-auto flex max-w-6xl flex-col items-center gap-4 px-4 py-8 text-center sm:flex-row sm:justify-between sm:px-6 sm:text-left">
                    <div>
                        <p className="font-semibold text-white">Recibe novedades</p>
                        <p className="mt-1 text-sm text-white/60">Automatización, IA y casos reales, sin spam.</p>
                    </div>
                    <div className="w-full sm:w-96">
                        <NewsletterForm />
                    </div>
                </div>
            </Reveal>

            <Reveal as="div" className="mx-auto grid max-w-6xl gap-12 px-4 py-14 sm:px-6 lg:grid-cols-[1.2fr_1.8fr]">
                <div>
                    {site?.branding?.logo_dark_url || site?.branding?.logo_url ? (
                        <img
                            src={site.branding.logo_dark_url ?? site.branding.logo_url}
                            alt={site.branding.site_name ?? 'Open9'}
                            className="h-8 w-auto"
                        />
                    ) : (
                        <p className="text-lg font-bold text-white">{site?.branding?.site_name ?? 'OPEN9'}</p>
                    )}

                    <p className="mt-4 max-w-sm text-sm text-white/60">
                        {site?.branding?.footer_description ?? site?.branding?.tagline ?? ''}
                    </p>

                    <ul className="mt-6 space-y-3">
                        {site?.contact?.email && (
                            <li>
                                <a href={`mailto:${site.contact.email}`} className="flex items-center gap-3 text-sm text-white/60 hover:text-white">
                                    <span className="icon-tile size-8 shrink-0"><Mail className="size-4" /></span>
                                    {site.contact.email}
                                </a>
                            </li>
                        )}
                        {site?.contact?.phone && (
                            <li>
                                <a href={`tel:${site.contact.phone.replace(/\s+/g, '')}`} className="flex items-center gap-3 text-sm text-white/60 hover:text-white">
                                    <span className="icon-tile size-8 shrink-0"><Phone className="size-4" /></span>
                                    {site.contact.phone}
                                </a>
                            </li>
                        )}
                        {site?.contact?.address && (
                            <li className="flex items-center gap-3 text-sm text-white/60">
                                <span className="icon-tile size-8 shrink-0"><MapPin className="size-4" /></span>
                                {site.contact.address}
                            </li>
                        )}
                    </ul>

                    {(site?.social_links ?? []).length > 0 && (
                        <div className="mt-6 flex gap-3">
                            {site.social_links.map((link) => (
                                <a
                                    key={link.platform}
                                    href={link.url}
                                    target="_blank"
                                    rel="noreferrer"
                                    aria-label={link.platform}
                                    className="icon-tile size-9 transition hover:border-tech-accent/60"
                                >
                                    <SocialIcon platform={link.platform} className="size-4" />
                                </a>
                            ))}
                        </div>
                    )}
                </div>

                {groups.length > 0 && (
                    <div className="grid grid-cols-2 gap-8 sm:grid-cols-4 lg:gap-6">
                        {groups.map((group) => (
                            <div key={group.title}>
                                <p className="text-sm font-semibold text-white">{group.title}</p>
                                <ul className="mt-3 space-y-2">
                                    {(group.links ?? []).map((link) => (
                                        <li key={link.url}>
                                            {link.is_external ? (
                                                <a href={link.url} target="_blank" rel="noreferrer" className="text-sm text-white/60 hover:text-white">
                                                    {link.label}
                                                </a>
                                            ) : (
                                                <Link to={link.url} className="text-sm text-white/60 hover:text-white">
                                                    {link.label}
                                                </Link>
                                            )}
                                        </li>
                                    ))}
                                </ul>
                            </div>
                        ))}
                    </div>
                )}
            </Reveal>

            <div className="border-t border-white/10 px-4 pb-24 pt-6 sm:px-6 sm:pb-6">
                <div className="mx-auto flex max-w-6xl flex-col items-center justify-between gap-3 sm:flex-row">
                    <p className="text-xs text-white/50">
                        {site?.branding?.copyright_text ?? `© ${new Date().getFullYear()} ${site?.branding?.site_name ?? 'OPEN9'}`}
                    </p>
                    <button type="button" onClick={scrollToTop} className="btn-ghost !px-3 !py-1.5 text-xs">
                        Volver arriba <ArrowUp className="size-3.5" />
                    </button>
                </div>
            </div>
        </footer>
    );
}
