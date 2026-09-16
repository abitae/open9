import { useId } from 'react';

export default function RocketIcon({ className = 'size-10', style }) {
    const id = useId();
    const flameId = `${id}-flame`;
    const bodyId = `${id}-body`;
    const windowId = `${id}-window`;

    return (
        <svg viewBox="0 0 100 168" className={className} style={style} fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <g className="rocket-flame">
                <ellipse cx="50" cy="148" rx="10" ry="16" fill="#00D26A" opacity="0.28" />
                <path d="M50 104 C36 122 32 138 50 160 C68 138 64 122 50 104Z" fill={`url(#${flameId})`} />
                <path d="M50 112 C42 126 40 138 50 152 C60 138 58 126 50 112Z" fill="#00D26A" />
                <path d="M50 118 C46 128 45 136 50 144 C55 136 54 128 50 118Z" fill="#FFE38C" />
            </g>

            <path d="M50 4C68 22 76 46 76 78C76 96 70 110 64 120H36C30 110 24 96 24 78C24 46 32 22 50 4Z" fill={`url(#${bodyId})`} />
            <path d="M50 4C58 22 64 46 64 78C64 96 61 110 58 120H42C39 110 36 96 36 78C36 46 42 22 50 4Z" fill="#E8EEFF" opacity="0.55" />

            <path d="M24 72C10 76 4 92 2 108C16 106 26 98 32 88L24 72Z" fill="#00D26A" />
            <path d="M76 72C90 76 96 92 98 108C84 106 74 98 68 88L76 72Z" fill="#0044AA" />
            <path d="M24 72C18 74 12 82 8 92C16 90 24 86 28 82L24 72Z" fill="#00A854" />

            <circle cx="50" cy="54" r="15" fill="#00A854" />
            <circle cx="50" cy="54" r="12" fill={`url(#${windowId})`} />
            <circle cx="46" cy="50" r="4" fill="#E8FFF3" opacity="0.85" />

            <path d="M38 120H62L58 134H42L38 120Z" fill="#0077FF" />
            <path d="M44 120H56L54 128H46L44 120Z" fill="#00D26A" />

            <defs>
                <linearGradient id={flameId} x1="50" y1="104" x2="50" y2="156" gradientUnits="userSpaceOnUse">
                    <stop stopColor="#00D26A" />
                    <stop offset="0.45" stopColor="#FF9E2C" />
                    <stop offset="1" stopColor="#FF4D2E" />
                </linearGradient>
                <linearGradient id={bodyId} x1="24" y1="4" x2="76" y2="120" gradientUnits="userSpaceOnUse">
                    <stop stopColor="#FFFFFF" />
                    <stop offset="1" stopColor="#C5D4FF" />
                </linearGradient>
                <radialGradient id={windowId} cx="50" cy="54" r="12" gradientUnits="userSpaceOnUse">
                    <stop stopColor="#B8FFE0" />
                    <stop offset="1" stopColor="#0077FF" />
                </radialGradient>
            </defs>
        </svg>
    );
}
