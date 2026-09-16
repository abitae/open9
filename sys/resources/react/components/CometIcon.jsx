import { useId } from 'react';

export default function CometIcon({ className = 'size-16', style }) {
    const id = useId();
    const dustId = `${id}-dust`;
    const ionId = `${id}-ion`;
    const coreId = `${id}-core`;

    return (
        <svg viewBox="0 0 240 72" className={className} style={style} fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path d="M8 44 C72 40 140 36 204 28" stroke={`url(#${dustId})`} strokeWidth="10" strokeLinecap="round" opacity="0.45" />
            <path d="M20 36 C84 22 148 18 208 24" stroke={`url(#${ionId})`} strokeWidth="4" strokeLinecap="round" opacity="0.7" />
            <path d="M36 50 C96 46 156 44 206 34" stroke="#00D26A" strokeWidth="2" strokeLinecap="round" opacity="0.35" />
            <circle cx="216" cy="28" r="11" fill={`url(#${coreId})`} />
            <circle cx="213" cy="25" r="3.5" fill="#F8FFFB" opacity="0.9" />
            <defs>
                <linearGradient id={dustId} x1="8" y1="44" x2="204" y2="28">
                    <stop stopColor="#00D26A" stopOpacity="0" />
                    <stop offset="0.55" stopColor="#7EC8FF" stopOpacity="0.25" />
                    <stop offset="1" stopColor="#E8FFF3" stopOpacity="0.8" />
                </linearGradient>
                <linearGradient id={ionId} x1="20" y1="36" x2="208" y2="24">
                    <stop stopColor="#0077FF" stopOpacity="0" />
                    <stop offset="1" stopColor="#B8FFE0" />
                </linearGradient>
                <radialGradient id={coreId} cx="38%" cy="32%" r="65%">
                    <stop stopColor="#FFFFFF" />
                    <stop offset="0.4" stopColor="#00D26A" />
                    <stop offset="1" stopColor="#0077FF" />
                </radialGradient>
            </defs>
        </svg>
    );
}
