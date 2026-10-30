const formatter = new Intl.RelativeTimeFormat(undefined, { numeric: 'auto' });

const units: Array<[Intl.RelativeTimeFormatUnit, number]> = [
    ['year', 1000 * 60 * 60 * 24 * 365],
    ['month', 1000 * 60 * 60 * 24 * 30],
    ['day', 1000 * 60 * 60 * 24],
    ['hour', 1000 * 60 * 60],
    ['minute', 1000 * 60],
];

export function relativeTime(iso: string): string {
    const diff = new Date(iso).getTime() - Date.now();

    for (const [unit, ms] of units) {
        if (Math.abs(diff) >= ms) {
            return formatter.format(Math.round(diff / ms), unit);
        }
    }

    return 'just now';
}
