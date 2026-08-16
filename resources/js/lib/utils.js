export function formatCurrency(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount);
}

export function formatDate(date) {
    return new Intl.DateTimeFormat('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    }).format(new Date(date));
}

export function formatMonth(month) {
    const date = new Date(2024, month - 1);
    return new Intl.DateTimeFormat('id-ID', { month: 'short' }).format(date);
}

export function cn(...classes) {
    return classes.filter(Boolean).join(' ');
}
