export function isValidEmail(value) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value.trim());
}

export function isValidPhone(value) {
    const trimmed = value.trim();
    const digits = trimmed.replace(/\D/g, '');

    return digits.length >= 6 && digits.length <= 15 && /^[+\d][\d\s().-]*$/.test(trimmed);
}
