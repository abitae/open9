import { useEffect, useRef, useState } from 'react';
import { MessageCircle, Send, X } from 'lucide-react';
import { api, ApiError } from '../lib/api';
import { useSite } from '../lib/site';
import { isValidEmail, isValidPhone } from '../lib/validate';

const STEP = {
    NAME: 'name',
    PHONE: 'phone',
    EMAIL: 'email',
    PROJECT: 'project',
    CHAT: 'chat',
};

export default function ChatWidget() {
    const { site } = useSite();
    const [open, setOpen] = useState(false);
    const [messages, setMessages] = useState([]);
    const [step, setStep] = useState(STEP.NAME);
    const [lead, setLead] = useState({ name: '', phone: '', email: '' });
    const [input, setInput] = useState('');
    const [isSending, setIsSending] = useState(false);
    const [error, setError] = useState('');
    const bottomRef = useRef(null);

    const chat = site?.chat;

    const addAssistantMessage = (text) => setMessages((current) => [...current, { role: 'assistant', text }]);

    useEffect(() => {
        if (open && messages.length === 0) {
            const intro = [chat?.welcome_message, '¿Cuál es tu nombre?'].filter(Boolean);
            setMessages(intro.map((text) => ({ role: 'assistant', text })));
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [open]);

    useEffect(() => {
        bottomRef.current?.scrollIntoView({ behavior: 'smooth' });
    }, [messages, open]);

    if (!chat?.is_enabled) {
        return null;
    }

    const submitLead = async (projectText) => {
        try {
            await api.post('/contact', {
                name: lead.name,
                email: lead.email,
                phone: lead.phone,
                subject: 'Lead desde chat IA',
                message: projectText,
            }, { auth: false });
        } catch {
            // El registro del lead no debe bloquear la conversación si falla;
            // el visitante ya recibió su confirmación en el chat.
        }
    };

    const handleSubmit = async (event) => {
        event.preventDefault();

        const text = input.trim();

        if (!text || isSending) {
            return;
        }

        setMessages((current) => [...current, { role: 'user', text }]);
        setInput('');
        setError('');

        if (step === STEP.NAME) {
            setLead((current) => ({ ...current, name: text }));
            addAssistantMessage(`Un gusto, ${text}. ¿Me compartes tu número de teléfono?`);
            setStep(STEP.PHONE);

            return;
        }

        if (step === STEP.PHONE) {
            if (!isValidPhone(text)) {
                addAssistantMessage('Ese teléfono no parece válido. ¿Puedes escribirlo de nuevo? (ej: 999 888 777)');

                return;
            }

            setLead((current) => ({ ...current, phone: text }));
            addAssistantMessage('Perfecto. ¿Cuál es tu correo electrónico?');
            setStep(STEP.EMAIL);

            return;
        }

        if (step === STEP.EMAIL) {
            if (!isValidEmail(text)) {
                addAssistantMessage('Ese correo no parece válido. ¿Puedes escribirlo de nuevo?');

                return;
            }

            setLead((current) => ({ ...current, email: text }));
            addAssistantMessage('Gracias. Cuéntame, ¿qué proceso o proyecto te gustaría automatizar?');
            setStep(STEP.PROJECT);

            return;
        }

        if (step === STEP.PROJECT) {
            setIsSending(true);
            await submitLead(text);
            addAssistantMessage(`Gracias, ${lead.name}. Ya registramos tus datos y en breve te contactaremos. Mientras tanto, con gusto respondo cualquier otra pregunta.`);
            setStep(STEP.CHAT);
            setIsSending(false);

            return;
        }

        const history = messages.map(({ role, text: messageText }) => ({ role, text: messageText }));
        setIsSending(true);

        try {
            const { reply } = await api.post('/chat', { message: text, history }, { auth: false });
            addAssistantMessage(reply);
        } catch (submitError) {
            setError(submitError instanceof ApiError ? submitError.message : 'No pudimos enviar tu mensaje. Intenta de nuevo.');
        } finally {
            setIsSending(false);
        }
    };

    return (
        <>
            {open && (
                <div className="ai-chat-panel">
                    <div className="flex items-center justify-between border-b border-white/10 p-4">
                        <p className="font-semibold text-white">{site?.branding?.site_name ?? 'OPEN9'} · Asistente</p>
                        <button type="button" onClick={() => setOpen(false)} aria-label="Cerrar chat" className="btn-ghost !px-2">
                            <X className="size-4" />
                        </button>
                    </div>

                    <div className="flex-1 space-y-3 overflow-y-auto p-4">
                        {messages.map((message, index) => (
                            <div
                                key={index}
                                className={`max-w-[85%] rounded-2xl px-4 py-2 text-sm ${message.role === 'user' ? 'ml-auto bg-brand text-white' : 'bg-white/10 text-white/90'}`}
                            >
                                {message.text}
                            </div>
                        ))}
                        {error && <p className="text-xs text-red-400">{error}</p>}
                        <div ref={bottomRef} />
                    </div>

                    <form onSubmit={handleSubmit} className="flex items-center gap-2 border-t border-white/10 p-3">
                        <input
                            value={input}
                            onChange={(event) => setInput(event.target.value)}
                            placeholder={
                                step === STEP.NAME ? 'Escribe tu nombre…'
                                    : step === STEP.PHONE ? 'Escribe tu teléfono…'
                                        : step === STEP.EMAIL ? 'Escribe tu correo…'
                                            : 'Escribe tu mensaje…'
                            }
                            className="contact-input flex-1 !py-2"
                            disabled={isSending}
                        />
                        <button type="submit" disabled={isSending || !input.trim()} aria-label="Enviar" className="btn-primary !rounded-full !p-2.5">
                            <Send className="size-4" />
                        </button>
                    </form>
                </div>
            )}

            <button type="button" onClick={() => setOpen((current) => !current)} className="ai-chat-fab">
                {open ? <X className="size-4" /> : <MessageCircle className="size-4" />}
                {!open && (chat.fab_label ?? 'Habla con nosotros')}
            </button>
        </>
    );
}
