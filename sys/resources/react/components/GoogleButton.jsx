export default function GoogleButton({ label = 'Continuar con Google' }) {
    const handleClick = () => {
        const returnTo = encodeURIComponent(window.location.origin);
        window.location.href = `/api/auth/google/redirect?return_to=${returnTo}`;
    };

    return (
        <button type="button" onClick={handleClick} className="btn-secondary w-full">
            {label}
        </button>
    );
}
