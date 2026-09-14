export default function RocketIcon({ className = 'size-10' }) {
    return (
        <svg viewBox="0 0 100 160" className={className} fill="none" xmlns="http://www.w3.org/2000/svg">
            <ellipse cx="50" cy="150" rx="16" ry="5" fill="#000" opacity="0.25" />

            <g className="rocket-flame">
                <path d="M50 108 C40 122 38 136 50 150 C62 136 60 122 50 108Z" fill="url(#rocket-flame-gradient)" />
                <path d="M50 116 C45 126 44 134 50 144 C56 134 55 126 50 116Z" fill="#FFE38C" />
            </g>

            <path d="M50 6C66 22 74 46 74 78C74 96 68 110 62 118H38C32 110 26 96 26 78C26 46 34 22 50 6Z" fill="#F5F7FF" />
            <path d="M50 6C58 22 62 46 62 78C62 96 59 110 56 118H44C41 110 38 96 38 78C38 46 42 22 50 6Z" fill="#DCE3FF" />

            <path d="M26 74C14 78 8 92 6 106C18 104 27 98 32 90L26 74Z" fill="#0077FF" />
            <path d="M74 74C86 78 92 92 94 106C82 104 73 98 68 90L74 74Z" fill="#0044AA" />

            <circle cx="50" cy="56" r="13" fill="#0077FF" />
            <circle cx="50" cy="56" r="8" fill="#BFE0FF" />

            <path d="M40 118H60L57 130H43L40 118Z" fill="#0077FF" />

            <defs>
                <linearGradient id="rocket-flame-gradient" x1="50" y1="108" x2="50" y2="150" gradientUnits="userSpaceOnUse">
                    <stop stopColor="#FF9E2C" />
                    <stop offset="1" stopColor="#FF4D2E" />
                </linearGradient>
            </defs>
        </svg>
    );
}
