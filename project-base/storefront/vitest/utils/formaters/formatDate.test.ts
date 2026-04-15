import { formatDate, formatDateAndTime, initIntlDateTimeFormatterLocale } from 'utils/formaters/formatDate';
import { beforeEach, describe, expect, test } from 'vitest';

describe('formatDate', () => {
    beforeEach(() => {
        initIntlDateTimeFormatterLocale('en');
    });

    describe('formatDate function', () => {
        test('formats ISO date string correctly', () => {
            const result = formatDate('2024-03-15T10:30:00Z', 'UTC');
            // Using localized short date format 'l'
            expect(result).toBe('3/15/2024');
        });

        test('formats date with timezone conversion', () => {
            // 2024-03-15 10:30 UTC should be different in other timezones
            const utcResult = formatDate('2024-03-15T10:30:00Z', 'UTC');
            expect(utcResult).toBe('3/15/2024');
        });

        test('handles date string without time', () => {
            const result = formatDate('2024-06-20', 'UTC');
            expect(result).toBe('6/20/2024');
        });

        test('formats leap year date correctly', () => {
            const result = formatDate('2024-02-29T12:00:00Z', 'UTC');
            expect(result).toBe('2/29/2024');
        });

        test('handles first day of year', () => {
            const result = formatDate('2024-01-01T00:00:00Z', 'UTC');
            expect(result).toBe('1/1/2024');
        });

        test('handles last day of year', () => {
            const result = formatDate('2024-12-31T23:59:59Z', 'UTC');
            expect(result).toBe('12/31/2024');
        });
    });

    describe('formatDateAndTime function', () => {
        test('formats date and time correctly', () => {
            const result = formatDateAndTime('2024-03-15T10:30:00Z', 'UTC');
            // Using localized short date format 'l LT' (date and time)
            expect(result).toBe('3/15/2024, 10:30 AM');
        });

        test('formats midnight correctly', () => {
            const result = formatDateAndTime('2024-03-15T00:00:00Z', 'UTC');
            expect(result).toBe('3/15/2024, 12:00 AM');
        });

        test('formats noon correctly', () => {
            const result = formatDateAndTime('2024-03-15T12:00:00Z', 'UTC');
            expect(result).toBe('3/15/2024, 12:00 PM');
        });

        test('formats afternoon time correctly', () => {
            const result = formatDateAndTime('2024-03-15T15:45:00Z', 'UTC');
            expect(result).toBe('3/15/2024, 3:45 PM');
        });
    });

    describe('initIntlDateTimeFormatterLocale function', () => {
        test('initializes Czech locale', () => {
            initIntlDateTimeFormatterLocale('cs');
            const result = formatDate('2024-03-15T10:30:00Z', 'UTC');
            // Czech locale uses d. m. yyyy format
            expect(result).toBe('15. 3. 2024');
        });

        test('initializes Slovak locale', () => {
            initIntlDateTimeFormatterLocale('sk');
            const result = formatDate('2024-03-15T10:30:00Z', 'UTC');
            // Slovak locale uses d. m. yyyy format
            expect(result).toBe('15. 3. 2024');
        });

        test('initializes English locale', () => {
            initIntlDateTimeFormatterLocale('en');
            const result = formatDate('2024-03-15T10:30:00Z', 'UTC');
            expect(result).toBe('3/15/2024');
        });
    });

    describe('Czech locale (cs) - formatDate', () => {
        beforeEach(() => {
            initIntlDateTimeFormatterLocale('cs');
        });

        test('formats ISO date string correctly', () => {
            const result = formatDate('2024-03-15T10:30:00Z', 'UTC');
            expect(result).toBe('15. 3. 2024');
        });

        test('handles date string without time', () => {
            const result = formatDate('2024-06-20', 'UTC');
            expect(result).toBe('20. 6. 2024');
        });

        test('formats leap year date correctly', () => {
            const result = formatDate('2024-02-29T12:00:00Z', 'UTC');
            expect(result).toBe('29. 2. 2024');
        });

        test('handles first day of year', () => {
            const result = formatDate('2024-01-01T00:00:00Z', 'UTC');
            expect(result).toBe('1. 1. 2024');
        });

        test('handles last day of year', () => {
            const result = formatDate('2024-12-31T23:59:59Z', 'UTC');
            expect(result).toBe('31. 12. 2024');
        });

        test('handles single digit day', () => {
            const result = formatDate('2024-03-05T10:30:00Z', 'UTC');
            expect(result).toBe('5. 3. 2024');
        });

        test('handles double digit day', () => {
            const result = formatDate('2024-03-25T10:30:00Z', 'UTC');
            expect(result).toBe('25. 3. 2024');
        });

        test('handles single digit month', () => {
            const result = formatDate('2024-05-15T10:30:00Z', 'UTC');
            expect(result).toBe('15. 5. 2024');
        });

        test('handles double digit month', () => {
            const result = formatDate('2024-11-15T10:30:00Z', 'UTC');
            expect(result).toBe('15. 11. 2024');
        });
    });

    describe('Czech locale (cs) - formatDateAndTime', () => {
        beforeEach(() => {
            initIntlDateTimeFormatterLocale('cs');
        });

        test('formats date and time correctly', () => {
            const result = formatDateAndTime('2024-03-15T10:30:00Z', 'UTC');
            expect(result).toBe('15. 3. 2024 10:30');
        });

        test('formats midnight correctly', () => {
            const result = formatDateAndTime('2024-03-15T00:00:00Z', 'UTC');
            expect(result).toBe('15. 3. 2024 0:00');
        });

        test('formats noon correctly', () => {
            const result = formatDateAndTime('2024-03-15T12:00:00Z', 'UTC');
            expect(result).toBe('15. 3. 2024 12:00');
        });

        test('formats afternoon time correctly', () => {
            const result = formatDateAndTime('2024-03-15T15:45:00Z', 'UTC');
            expect(result).toBe('15. 3. 2024 15:45');
        });

        test('formats evening time correctly', () => {
            const result = formatDateAndTime('2024-03-15T21:30:00Z', 'UTC');
            expect(result).toBe('15. 3. 2024 21:30');
        });

        test('formats early morning time correctly', () => {
            const result = formatDateAndTime('2024-03-15T05:15:00Z', 'UTC');
            expect(result).toBe('15. 3. 2024 5:15');
        });
    });

    describe('Czech locale (cs) - timezone handling', () => {
        beforeEach(() => {
            initIntlDateTimeFormatterLocale('cs');
        });

        test('handles Europe/Prague timezone', () => {
            const result = formatDateAndTime('2024-03-15T10:30:00Z', 'Europe/Prague');
            expect(result).toBe('15. 3. 2024 11:30');
        });

        test('handles date crossing boundary in Europe/Prague timezone', () => {
            // 2024-01-01 00:30 UTC is 01:30 in Prague
            const result = formatDateAndTime('2024-01-01T00:30:00Z', 'Europe/Prague');
            expect(result).toBe('1. 1. 2024 1:30');
        });

        test('handles late night crossing to next day in Prague', () => {
            // 2024-03-15 23:30 UTC is 2024-03-16 00:30 in Prague (during DST)
            const result = formatDateAndTime('2024-03-15T23:30:00Z', 'Europe/Prague');
            expect(result).toBe('16. 3. 2024 0:30');
        });

        test('handles summer time in Prague', () => {
            // July is in summer time (CEST, UTC+2)
            const result = formatDateAndTime('2024-07-15T10:00:00Z', 'Europe/Prague');
            expect(result).toBe('15. 7. 2024 12:00');
        });

        test('handles winter time in Prague', () => {
            // January is in winter time (CET, UTC+1)
            const result = formatDateAndTime('2024-01-15T10:00:00Z', 'Europe/Prague');
            expect(result).toBe('15. 1. 2024 11:00');
        });
    });

    describe('timezone handling', () => {
        test('handles Europe/Prague timezone', () => {
            initIntlDateTimeFormatterLocale('cs');
            // Prague is UTC+1 in winter, UTC+2 in summer
            // March 30, 2024 is in winter time (CET, UTC+1)
            const result = formatDateAndTime('2024-03-30T10:30:00Z', 'Europe/Prague');
            // 10:30 UTC = 11:30 CET
            expect(result).toBe('30. 3. 2024 11:30');
        });

        test('handles Europe/Prague timezone', () => {
            initIntlDateTimeFormatterLocale('cs');
            // Prague is UTC+1 in winter, UTC+2 in summer
            // March 31, 2024 before 2am is in winter time (CET, UTC+1)
            const resultCET = formatDateAndTime('2024-03-31T00:59:59Z', 'Europe/Prague');
            // March 31, 2024 after 2am is in summer time (CEST, UTC+2)
            const resultCEST = formatDateAndTime('2024-03-31T01:00:00Z', 'Europe/Prague');

            // 00:59:59 UTC = 01:59:59 CET in Prague
            expect(resultCET).toBe('31. 3. 2024 1:59');
            // 01:00:00 UTC = 03:00:00 CEST in Prague
            expect(resultCEST).toBe('31. 3. 2024 3:00');
        });

        test('handles America/New_York timezone', () => {
            // New York is UTC-5 in winter, UTC-4 in summer
            // March 15, 2024 is after DST change (EDT, UTC-4)
            initIntlDateTimeFormatterLocale('en');
            const result = formatDateAndTime('2024-03-15T10:30:00Z', 'America/New_York');
            // 10:30 UTC = 6:30 EDT
            expect(result).toBe('3/15/2024, 6:30 AM');
        });

        test('handles date crossing boundary in different timezone', () => {
            initIntlDateTimeFormatterLocale('en');
            // 2024-01-01 02:00 UTC is still Dec 31 in New York (UTC-5)
            const result = formatDate('2024-01-01T02:00:00Z', 'America/New_York');
            expect(result).toBe('12/31/2023');
        });
    });

    describe('edge cases', () => {
        test('handles undefined date', () => {
            const result = formatDate(undefined, 'UTC');
            // Returns default `new Date()` date when date is undefined
            expect(result).toBe(formatDate(new Date(), 'UTC'));
        });

        test('handles empty string date', () => {
            const result = formatDate('', 'UTC');
            // Returns default `new Date()` date when date is empty string
            expect(result).toBe(formatDate(new Date(), 'UTC'));
        });

        test('handles invalid date', () => {
            const result = formatDate('invalid', 'UTC');
            // Returns empty string when date is invalid
            expect(result).toBe('');
        });

        test('handles undefined timezone', () => {
            const result = formatDate('2024-03-15T10:30:00Z', undefined);
            expect(result).toBeDefined();
            expect(typeof result).toBe('string');
        });
    });
});
