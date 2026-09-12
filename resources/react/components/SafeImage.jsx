import { useState } from 'react';

/**
 * Reemplazo directo de <img> que se oculta a sí misma si la imagen falla al
 * cargar, en vez de dejar el ícono de imagen rota superpuesto con el texto
 * (p. ej. fotos de referencia externas que el proveedor eliminó).
 */
export default function SafeImage({ src, alt = '', ...rest }) {
    const [failed, setFailed] = useState(false);

    if (!src || failed) {
        return null;
    }

    return <img src={src} alt={alt} onError={() => setFailed(true)} {...rest} />;
}
