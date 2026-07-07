import { createIntlDateTimeFormatter, initIntlDateTimeFormatterLocale } from './createIntlDateTimeFormatter';

export { initIntlDateTimeFormatterLocale };

const parseDate = (date?: Date | string): Date | null => {
    const dateObj = date ? new Date(date) : new Date();
    if (Number.isNaN(dateObj.getTime())) {
        return null;
    }
    return dateObj;
};

/**
 * Formats a date string using the provided or current locale and optional timezone.
 * Returns localized short date format (e.g., "3/15/2024" for English, "15. 3. 2024" for Czech).
 */
export const formatDate = (date?: Date | string, timezone?: string, locale?: string): string => {
    const dateObj = parseDate(date);
    if (!dateObj) {
        return '';
    }

    const formatter = createIntlDateTimeFormatter(
        {
            year: 'numeric',
            month: 'numeric',
            day: 'numeric',
        },
        timezone,
        locale,
    );

    return formatter.format(dateObj);
};

/**
 * Formats a date and time string using the provided or current locale and optional timezone.
 * Returns localized short date and time format (e.g., "3/15/2024 10:30 AM" for English).
 */
export const formatDateAndTime = (date?: Date | string, timezone?: string, locale?: string): string => {
    const dateObj = parseDate(date);
    if (!dateObj) {
        return '';
    }

    const formatter = createIntlDateTimeFormatter(
        {
            year: 'numeric',
            month: 'numeric',
            day: 'numeric',
            hour: 'numeric',
            minute: '2-digit',
        },
        timezone,
        locale,
    );

    return formatter.format(dateObj);
};
