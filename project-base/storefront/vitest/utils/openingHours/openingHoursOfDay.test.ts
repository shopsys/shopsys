import {
    findOpeningHoursOfDayForDate,
    getIsoDayOfWeek,
    isSameLocalDay,
    OpeningHoursOfDay,
} from 'utils/openingHours/openingHoursOfDay';
import { describe, expect, test } from 'vitest';

const createOpeningHoursOfDay = (date: string, dayOfWeek: number): OpeningHoursOfDay => ({
    date,
    dayOfWeek,
    openingHoursRanges: [{ openingTime: '08:00', closingTime: '18:00' }],
});

describe('getIsoDayOfWeek', () => {
    test('returns 1 for Monday and 7 for Sunday', () => {
        expect(getIsoDayOfWeek(new Date('2026-07-13T12:00:00'))).toBe(1);
        expect(getIsoDayOfWeek(new Date('2026-07-19T12:00:00'))).toBe(7);
    });
});

describe('isSameLocalDay', () => {
    test('compares only the date part', () => {
        expect(isSameLocalDay(new Date('2026-07-13T00:00:00'), new Date('2026-07-13T23:59:59'))).toBe(true);
        expect(isSameLocalDay(new Date('2026-07-13T23:59:59'), new Date('2026-07-14T00:00:00'))).toBe(false);
    });
});

describe('findOpeningHoursOfDayForDate', () => {
    const week = [
        createOpeningHoursOfDay('2026-07-16T00:00:00', 4),
        createOpeningHoursOfDay('2026-07-17T00:00:00', 5),
        createOpeningHoursOfDay('2026-07-18T00:00:00', 6),
    ];

    test('prefers the day matching the exact date', () => {
        expect(findOpeningHoursOfDayForDate(week, new Date('2026-07-17T12:00:00'))).toBe(week[1]);
    });

    test('falls back to the same day of the week for a date beyond the provided days', () => {
        // 2026-07-24 is a Friday too
        expect(findOpeningHoursOfDayForDate(week, new Date('2026-07-24T12:00:00'))).toBe(week[1]);
    });

    test('returns null when no day matches at all', () => {
        // 2026-07-20 is a Monday, which is missing in the provided days
        expect(findOpeningHoursOfDayForDate(week, new Date('2026-07-20T12:00:00'))).toBeNull();
    });
});
