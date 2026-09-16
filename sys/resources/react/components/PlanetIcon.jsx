import { useId } from 'react';

function Lighting({ id, cx, cy, r }) {
    return (
        <>
            <circle cx={cx} cy={cy} r={r} fill={`url(#${id}-term)`} />
            <circle cx={cx} cy={cy} r={r + 2} fill="none" stroke={`url(#${id}-halo)`} strokeWidth="3" opacity="0.7" />
        </>
    );
}

function LightingDefs({ id }) {
    return (
        <>
            <linearGradient id={`${id}-term`} x1="18%" y1="12%" x2="92%" y2="88%">
                <stop offset="0.28" stopColor="#000" stopOpacity="0" />
                <stop offset="0.62" stopColor="#000" stopOpacity="0.28" />
                <stop offset="1" stopColor="#000" stopOpacity="0.62" />
            </linearGradient>
            <radialGradient id={`${id}-halo`} cx="38%" cy="32%" r="70%">
                <stop offset="0.72" stopColor="#7EC8FF" stopOpacity="0" />
                <stop offset="1" stopColor="#7EC8FF" stopOpacity="0.55" />
            </radialGradient>
        </>
    );
}

export default function PlanetIcon({ variant = 'earth', className = 'size-16' }) {
    const id = useId();
    const sphereId = `${id}-sphere`;
    const glowId = `${id}-glow`;
    const ringId = `${id}-ring`;
    const gasId = `${id}-gas`;
    const clipId = `${id}-clip`;

    if (variant === 'ring') {
        return (
            <svg viewBox="0 0 140 100" className={className} fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <ellipse cx="70" cy="52" rx="58" ry="14" fill={`url(#${ringId})`} opacity="0.4" />
                <ellipse cx="70" cy="52" rx="58" ry="14" stroke="#00D26A" strokeWidth="2" opacity="0.55" />
                <g className="planet-spin origin-center">
                    <circle cx="70" cy="50" r="28" fill={`url(#${sphereId})`} />
                    <circle cx="58" cy="42" r="6" fill="#B8FFE0" opacity="0.28" />
                    <circle cx="78" cy="56" r="4" fill="#0044AA" opacity="0.4" />
                </g>
                <Lighting id={id} cx="70" cy="50" r="28" />
                <ellipse cx="70" cy="52" rx="58" ry="14" stroke="#0077FF" strokeWidth="1.5" opacity="0.45" clipPath={`url(#${clipId})`} />
                <defs>
                    <clipPath id={clipId}>
                        <rect x="12" y="52" width="116" height="20" />
                    </clipPath>
                    <radialGradient id={sphereId} cx="36%" cy="30%" r="72%">
                        <stop stopColor="#C9E4FF" />
                        <stop offset="0.4" stopColor="#5AA4FF" />
                        <stop offset="1" stopColor="#123A7A" />
                    </radialGradient>
                    <linearGradient id={ringId} x1="12" y1="52" x2="128" y2="52">
                        <stop stopColor="#00D26A" stopOpacity="0" />
                        <stop offset="0.3" stopColor="#00D26A" />
                        <stop offset="0.7" stopColor="#0077FF" />
                        <stop offset="1" stopColor="#0077FF" stopOpacity="0" />
                    </linearGradient>
                    <LightingDefs id={id} />
                </defs>
            </svg>
        );
    }

    if (variant === 'gas') {
        return (
            <svg viewBox="0 0 100 100" className={className} fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <circle cx="50" cy="50" r="40" fill="#00D26A" opacity="0.12" />
                <g className="planet-spin origin-center">
                    <circle cx="50" cy="50" r="36" fill={`url(#${gasId})`} />
                    <path d="M16 42 C32 38 48 46 68 40 C78 38 86 42 88 48 C70 52 48 44 28 50 C20 52 16 48 16 42Z" fill="#00D26A" opacity="0.4" clipPath={`url(#${clipId})`} />
                    <path d="M18 58 C36 62 54 54 78 60 C86 62 88 64 86 70 C64 66 42 74 22 68 C16 66 16 62 18 58Z" fill="#08301F" opacity="0.45" clipPath={`url(#${clipId})`} />
                </g>
                <Lighting id={id} cx="50" cy="50" r="36" />
                <defs>
                    <clipPath id={clipId}>
                        <circle cx="50" cy="50" r="36" />
                    </clipPath>
                    <radialGradient id={gasId} cx="34%" cy="28%" r="75%">
                        <stop stopColor="#D4FFE8" />
                        <stop offset="0.4" stopColor="#00A854" />
                        <stop offset="1" stopColor="#062418" />
                    </radialGradient>
                    <LightingDefs id={id} />
                </defs>
            </svg>
        );
    }

    return (
        <svg viewBox="0 0 100 100" className={className} fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <circle cx="50" cy="50" r="40" fill="#0077FF" opacity="0.14" />
            <g className="planet-spin origin-center">
                <circle cx="50" cy="50" r="36" fill={`url(#${sphereId})`} />
                <g clipPath={`url(#${clipId})`}>
                    <path d="M28 38 C34 30 46 32 52 38 C58 34 68 36 72 44 C64 48 52 42 44 48 C36 52 30 46 28 38Z" fill="#00D26A" />
                    <path d="M32 62 C40 58 50 64 60 60 C68 66 62 76 50 78 C40 76 30 70 32 62Z" fill="#00A854" />
                    <path d="M58 28 C64 26 70 30 68 36 C62 38 56 34 58 28Z" fill="#147A48" />
                    <circle cx="40" cy="34" r="8" fill={`url(#${glowId})`} />
                </g>
            </g>
            <Lighting id={id} cx="50" cy="50" r="36" />
            <defs>
                <clipPath id={clipId}>
                    <circle cx="50" cy="50" r="36" />
                </clipPath>
                <radialGradient id={sphereId} cx="32%" cy="26%" r="75%">
                    <stop stopColor="#B9E4FF" />
                    <stop offset="0.42" stopColor="#1A7DFF" />
                    <stop offset="1" stopColor="#071F4A" />
                </radialGradient>
                <radialGradient id={glowId} cx="50%" cy="50%" r="50%">
                    <stop stopColor="#E8FFF3" stopOpacity="0.55" />
                    <stop offset="1" stopColor="#E8FFF3" stopOpacity="0" />
                </radialGradient>
                <LightingDefs id={id} />
            </defs>
        </svg>
    );
}
