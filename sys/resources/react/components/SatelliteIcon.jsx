import { useId } from 'react';

export default function SatelliteIcon({ className = 'size-12', style }) {
    const id = useId();
    const bodyId = `${id}-body`;
    const panelId = `${id}-panel`;

    return (
        <svg viewBox="0 0 120 80" className={className} style={style} fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <rect x="8" y="28" width="28" height="24" rx="2" fill={`url(#${panelId})`} stroke="#00D26A" strokeWidth="1.5" />
            <path d="M12 34H32M12 40H32M12 46H32" stroke="#E8FFF3" strokeWidth="1" opacity="0.5" />
            <rect x="84" y="28" width="28" height="24" rx="2" fill={`url(#${panelId})`} stroke="#00D26A" strokeWidth="1.5" />
            <path d="M88 34H108M88 40H108M88 46H108" stroke="#E8FFF3" strokeWidth="1" opacity="0.5" />

            <rect x="42" y="30" width="36" height="20" rx="4" fill={`url(#${bodyId})`} />
            <circle cx="60" cy="40" r="5" fill="#00D26A" />
            <circle cx="60" cy="40" r="2.5" fill="#B8FFE0" />

            <path d="M60 30V14" stroke="#C5D4FF" strokeWidth="2" />
            <circle cx="60" cy="10" r="5" stroke="#0077FF" strokeWidth="2" fill="none" />
            <path d="M60 50V66" stroke="#C5D4FF" strokeWidth="2" />
            <circle cx="60" cy="70" r="3" fill="#00D26A" />

            <defs>
                <linearGradient id={bodyId} x1="42" y1="30" x2="78" y2="50">
                    <stop stopColor="#F5F7FF" />
                    <stop offset="1" stopColor="#8AA4E0" />
                </linearGradient>
                <linearGradient id={panelId} x1="8" y1="28" x2="36" y2="52">
                    <stop stopColor="#0044AA" />
                    <stop offset="1" stopColor="#00A854" />
                </linearGradient>
            </defs>
        </svg>
    );
}
