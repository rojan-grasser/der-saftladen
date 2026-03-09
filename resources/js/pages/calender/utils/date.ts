export const parseDate = (value: string | number | null | undefined) => {
    if (value === null || value === undefined || value === '') {
        return null;
    }

    if (typeof value === 'number') {
        const milliseconds = value > 9999999999 ? value : value * 1000;
        const parsed = new Date(milliseconds);
        return Number.isNaN(parsed.getTime()) ? null : parsed;
    }

    const trimmed = value.trim();
    if (!trimmed) {
        return null;
    }

    if (/^\d+$/.test(trimmed)) {
        const asNumber = Number(trimmed);
        const milliseconds = asNumber > 9999999999 ? asNumber : asNumber * 1000;
        const parsed = new Date(milliseconds);
        return Number.isNaN(parsed.getTime()) ? null : parsed;
    }

    const normalized = trimmed.includes('T')
        ? trimmed
        : trimmed.replace(' ', 'T');
    const parsed = new Date(normalized);
    return Number.isNaN(parsed.getTime()) ? null : parsed;
};

export const toUnixSeconds = (value: string | number) => {
    const parsed = parseDate(value);
    return parsed ? Math.floor(parsed.getTime() / 1000) : null;
};

export const toDateKey = (value: Date) => {
    return `${value.getFullYear()}-${String(value.getMonth() + 1).padStart(
        2,
        '0',
    )}-${String(value.getDate()).padStart(2, '0')}`;
};

export const toInputDateTime = (value: Date) => {
    return `${value.getFullYear()}-${String(value.getMonth() + 1).padStart(
        2,
        '0',
    )}-${String(value.getDate()).padStart(2, '0')}T${String(
        value.getHours(),
    ).padStart(2, '0')}:${String(value.getMinutes()).padStart(2, '0')}`;
};

export const toInputDate = (value: Date) => {
    return `${value.getFullYear()}-${String(value.getMonth() + 1).padStart(
        2,
        '0',
    )}-${String(value.getDate()).padStart(2, '0')}`;
};

export const toInputTime = (value: Date) => {
    return `${String(value.getHours()).padStart(2, '0')}:${String(
        value.getMinutes(),
    ).padStart(2, '0')}`;
};

export const toDateString = (value: string | number | null | undefined) => {
    if (value === null || value === undefined || value === '') {
        return '';
    }
    const parsed = parseDate(value);
    return parsed ? toInputDate(parsed) : '';
};

export const toTimeString = (value: string | number | null | undefined) => {
    if (value === null || value === undefined || value === '') {
        return '';
    }
    const parsed = parseDate(value);
    return parsed ? toInputTime(parsed) : '';
};

export const addMinutes = (value: Date, minutes: number) => {
    const next = new Date(value);
    next.setMinutes(next.getMinutes() + minutes);
    return next;
};

export const isSameDay = (a: Date, b: Date) => {
    return (
        a.getFullYear() === b.getFullYear() &&
        a.getMonth() === b.getMonth() &&
        a.getDate() === b.getDate()
    );
};

export const startOfWeek = (value: Date) => {
    const start = new Date(value);
    const day = (start.getDay() + 6) % 7;
    start.setDate(start.getDate() - day);
    start.setHours(0, 0, 0, 0);
    return start;
};

export const toDayStart = (value: Date) => {
    const date = new Date(value);
    date.setHours(0, 0, 0, 0);
    return date;
};
