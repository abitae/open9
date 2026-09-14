import PageHeader from '../components/PageHeader';
import ContactForm from '../components/ContactForm';
import { useSite } from '../lib/site';

export default function ContactPage() {
    const { site } = useSite();

    return (
        <div>
            <PageHeader eyebrow="Contacto" title="Cuéntanos qué necesitas automatizar" />

            <div className="mx-auto grid max-w-5xl gap-10 px-4 pb-20 sm:px-6 md:grid-cols-[2fr_1fr]">
                <ContactForm />

                <aside className="space-y-4 text-sm text-white/70">
                    {site?.contact?.email && (
                        <div>
                            <p className="font-semibold text-white">Correo</p>
                            <p>{site.contact.email}</p>
                        </div>
                    )}
                    {site?.contact?.phone && (
                        <div>
                            <p className="font-semibold text-white">Teléfono</p>
                            <p>{site.contact.phone}</p>
                        </div>
                    )}
                    {site?.contact?.address && (
                        <div>
                            <p className="font-semibold text-white">Dirección</p>
                            <p>{site.contact.address}</p>
                        </div>
                    )}
                </aside>
            </div>
        </div>
    );
}
