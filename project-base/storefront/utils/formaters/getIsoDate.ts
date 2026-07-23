import { createIntlDateTimeFormatter } from './createIntlDateTimeFormatter';

/**
 * Returns the YYYY-MM-DD calendar day of the given ISO 8601 date-time in the given timezone;
 * undefined when the value is not a valid date-time
 */
export const getIsoDate = (isoDateTime: string, timezone: string): string | undefined => {
    const date = new Date(isoDateTime);

    if (Number.isNaN(date.getTime())) {
        return undefined;
    }

    // en-CA formats the date as YYYY-MM-DD
    return createIntlDateTimeFormatter({ year: 'numeric', month: '2-digit', day: '2-digit' }, timezone, 'en-CA').format(
        date,
    );
};
