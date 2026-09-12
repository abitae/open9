import { useState } from 'react';
import { api, ApiError } from '../lib/api';

const INITIAL = { name: '', email: '', phone: '', company: '', subject: '', message: '' };

export default function ContactForm() {
    const [form, setForm] = useState(INITIAL);
    const [errors, setErrors] = useState({});
    const [status, setStatus] = useState('idle');

    const update = (field) => (event) => {
        setForm((current) => ({ ...current, [field]: event.target.value }));
    };

    const handleSubmit = async (event) => {
        event.preventDefault();
        setStatus('sending');
        setErrors({});

        try {
            await api.post('/contact', form, { auth: false });
            setStatus('sent');
            setForm(INITIAL);
        } catch (error) {
            if (error instanceof ApiError && error.errors) {
                setErrors(error.errors);
            }

            setStatus('error');
        }
    };

    if (status === 'sent') {
        return (
            <div className="rounded-2xl border border-white/10 bg-white/5 p-8 text-center">
                <p className="text-lg font-semibold text-white">Mensaje enviado. Te responderemos pronto.</p>
            </div>
        );
    }

    return (
        <form onSubmit={handleSubmit} className="grid gap-4">
            <div className="grid gap-4 sm:grid-cols-2">
                <div className="contact-form-field">
                    <label htmlFor="contact-name" className="text-sm text-white/70">Nombre completo</label>
                    <input id="contact-name" required className="contact-input" value={form.name} onChange={update('name')} />
                    {errors.name && <p className="text-xs text-red-400">{errors.name[0]}</p>}
                </div>
                <div className="contact-form-field">
                    <label htmlFor="contact-email" className="text-sm text-white/70">Correo electrónico</label>
                    <input id="contact-email" type="email" required className="contact-input" value={form.email} onChange={update('email')} />
                    {errors.email && <p className="text-xs text-red-400">{errors.email[0]}</p>}
                </div>
            </div>

            <div className="grid gap-4 sm:grid-cols-2">
                <div className="contact-form-field">
                    <label htmlFor="contact-phone" className="text-sm text-white/70">Teléfono (opcional)</label>
                    <input id="contact-phone" className="contact-input" value={form.phone} onChange={update('phone')} />
                </div>
                <div className="contact-form-field">
                    <label htmlFor="contact-company" className="text-sm text-white/70">Empresa (opcional)</label>
                    <input id="contact-company" className="contact-input" value={form.company} onChange={update('company')} />
                </div>
            </div>

            <div className="contact-form-field">
                <label htmlFor="contact-subject" className="text-sm text-white/70">Asunto (opcional)</label>
                <select id="contact-subject" className="contact-input" value={form.subject} onChange={update('subject')}>
                    <option value="">Selecciona un asunto</option>
                    <option value="Automatización e IA">Automatización e IA</option>
                    <option value="Hardware y servidores">Hardware y servidores</option>
                    <option value="Cloud">Cloud</option>
                    <option value="Otro">Otro</option>
                </select>
            </div>

            <div className="contact-form-field">
                <label htmlFor="contact-message" className="text-sm text-white/70">Mensaje</label>
                <textarea id="contact-message" required rows={5} className="contact-input" value={form.message} onChange={update('message')} />
                {errors.message && <p className="text-xs text-red-400">{errors.message[0]}</p>}
            </div>

            {status === 'error' && Object.keys(errors).length === 0 && (
                <p className="text-sm text-red-400">No pudimos enviar tu mensaje. Intenta de nuevo.</p>
            )}

            <button type="submit" disabled={status === 'sending'} className="btn-primary justify-self-start">
                {status === 'sending' ? 'Enviando…' : 'Enviar mensaje'}
            </button>
        </form>
    );
}
