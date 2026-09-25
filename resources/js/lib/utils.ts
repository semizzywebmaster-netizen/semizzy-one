/* ============================================
   SEMIZZY ONE — Utility Functions
   ============================================ */

export function cn(...classes: (string | boolean | undefined | null)[]): string {
    return classes.filter(Boolean).join(' ');
}

export function formatDate(date: string | Date, format: string = 'medium'): string {
    const d = new Date(date);
    const options: Intl.DateTimeFormatOptions = {
        year: 'numeric',
        month: format === 'short' ? 'short' : 'long',
        day: 'numeric',
    };
    return d.toLocaleDateString(undefined, options);
}

export function formatDateTime(date: string | Date): string {
    const d = new Date(date);
    return d.toLocaleString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

export function timeAgo(date: string | Date): string {
    const now = new Date();
    const d = new Date(date);
    const seconds = Math.floor((now.getTime() - d.getTime()) / 1000);

    const intervals = [
        { label: 'year', seconds: 31536000 },
        { label: 'month', seconds: 2592000 },
        { label: 'week', seconds: 604800 },
        { label: 'day', seconds: 86400 },
        { label: 'hour', seconds: 3600 },
        { label: 'minute', seconds: 60 },
    ];

    for (const interval of intervals) {
        const count = Math.floor(seconds / interval.seconds);
        if (count > 0) {
            return `${count} ${interval.label}${count !== 1 ? 's' : ''} ago`;
        }
    }

    return 'just now';
}

export function generateId(): string {
    return `${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;
}

export function debounce<T extends (...args: unknown[]) => void>(
    func: T,
    wait: number
): (...args: Parameters<T>) => void {
    let timeout: ReturnType<typeof setTimeout>;
    return (...args: Parameters<T>) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => func(...args), wait);
    };
}

export function truncate(str: string, length: number): string {
    if (str.length <= length) return str;
    return str.slice(0, length) + '...';
}

export function capitalize(str: string): string {
    return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
}

export function slugify(str: string): string {
    return str
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/(^-|-$)/g, '');
}