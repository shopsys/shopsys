import { createIntlDateTimeFormatter } from 'utils/formaters/createIntlDateTimeFormatter';

import { getStringFromUrlQuery } from './getStringFromUrlQuery';

const DATE_QUERY_PATTERN = /^(\d{4})-(\d{2})-(\d{2})$/;
const DATE_TIME_FORMAT_LOCALE = 'en-US';
const UTC_TIMEZONE_SUFFIX = '+00:00';

export const getDateTimeFromDateQuery = (
    dateQuery: string | string[] | undefined,
    isEndOfDay: boolean,
    timezone: string,
): string | null => {
    const date = getStringFromUrlQuery(dateQuery);

    if (!isDateQueryValid(date)) {
        return null;
    }

    const [year, month, day] = getDateParts(date);
    const [hours, minutes, seconds] = isEndOfDay ? [23, 59, 59] : [0, 0, 0];
    const dateTimeInUtc = getUtcDateTimeFromTimezoneDateTime(year, month, day, hours, minutes, seconds, timezone);

    return formatDateTimeForGraphql(dateTimeInUtc);
};

export const isDateQueryValid = (date: string): boolean => {
    const dateMatch = date.match(DATE_QUERY_PATTERN);

    if (!dateMatch) {
        return false;
    }

    const [year, month, day] = getDateParts(date);
    const parsedDate = new Date(year, month - 1, day);

    return parsedDate.getFullYear() === year && parsedDate.getMonth() === month - 1 && parsedDate.getDate() === day;
};

const getDateParts = (date: string): [number, number, number] => {
    const [, year, month, day] = date.match(DATE_QUERY_PATTERN)!.map(Number);

    return [year, month, day];
};

const getUtcDateTimeFromTimezoneDateTime = (
    year: number,
    month: number,
    day: number,
    hours: number,
    minutes: number,
    seconds: number,
    timezone: string,
): Date => {
    const utcDateTime = Date.UTC(year, month - 1, day, hours, minutes, seconds);
    const firstOffset = getTimezoneOffsetInMinutes(new Date(utcDateTime), timezone);
    const firstUtcDateTime = utcDateTime - firstOffset * 60_000;
    const finalOffset = getTimezoneOffsetInMinutes(new Date(firstUtcDateTime), timezone);

    return new Date(utcDateTime - finalOffset * 60_000);
};

const getTimezoneOffsetInMinutes = (date: Date, timezone: string): number => {
    const dateTimeFormat = createIntlDateTimeFormatter(
        {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hourCycle: 'h23',
        },
        timezone,
        DATE_TIME_FORMAT_LOCALE,
    );
    const parts = dateTimeFormat.formatToParts(date);
    const getPartValue = (type: Intl.DateTimeFormatPartTypes) =>
        Number(parts.find((part) => part.type === type)?.value);
    const timezoneDateAsUtc = Date.UTC(
        getPartValue('year'),
        getPartValue('month') - 1,
        getPartValue('day'),
        getPartValue('hour'),
        getPartValue('minute'),
        getPartValue('second'),
    );

    return (timezoneDateAsUtc - date.getTime()) / 60_000;
};

const formatDateTimeForGraphql = (date: Date): string => date.toISOString().replace('.000Z', UTC_TIMEZONE_SUFFIX);
