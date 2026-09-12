export function formatMoney(amount, currency = 'USD') {
    const value = Number(amount ?? 0);
    const locale = currency === 'PEN' ? 'es-PE' : 'en-US';

    return new Intl.NumberFormat(locale, {
        style: 'currency',
        currency,
        minimumFractionDigits: 2,
    }).format(value);
}

export function formatDate(value) {
    if (!value) {
        return '';
    }

    return new Intl.DateTimeFormat('es-PE', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(new Date(value));
}
