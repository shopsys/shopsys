/**
 * Formats a time string for accessible display based on locale.
 * - English: 12-hour format with AM/PM (e.g., "9 AM", "2:30 PM")
 * - Other locales: 24-hour format (e.g., "9:00", "14:30")
 */
export const formatAccessibleTime = (timeString: string, locale: string = 'en'): string => {
    if (!timeString || typeof timeString !== 'string' || !timeString.includes(':')) {
        return '';
    }

    const [hoursStr, minutesStr] = timeString.split(':');
    const hours = parseInt(hoursStr, 10);
    const minutes = parseInt(minutesStr, 10);

    if (Number.isNaN(hours) || Number.isNaN(minutes) || hours < 0 || hours > 23 || minutes < 0 || minutes > 59) {
        return '';
    }

    const use24HourFormat = locale !== 'en';

    if (use24HourFormat) {
        return `${hours}:${minutes.toString().padStart(2, '0')}`;
    }

    const period = hours >= 12 ? 'PM' : 'AM';
    let hours12 = hours % 12;
    if (hours12 === 0) {
        hours12 = 12;
    }

    if (minutes === 0) {
        return `${hours12} ${period}`;
    }
    return `${hours12}:${minutes.toString().padStart(2, '0')} ${period}`;
};
