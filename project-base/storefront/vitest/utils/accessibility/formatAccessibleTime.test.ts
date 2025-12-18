import { formatAccessibleTime } from 'utils/accessibility/formatAccessibleTime';
import { describe, expect, test } from 'vitest';

describe('formatAccessibleTime', () => {
    describe('12-hour format (English locale)', () => {
        test('formats time with AM correctly', () => {
            const result = formatAccessibleTime('09:30', 'en');
            expect(result).toBe('9:30 AM');
        });

        test('formats time with PM correctly', () => {
            const result = formatAccessibleTime('14:30', 'en');
            expect(result).toBe('2:30 PM');
        });

        test('formats midnight as 12 AM', () => {
            const result = formatAccessibleTime('00:00', 'en');
            expect(result).toBe('12 AM');
        });

        test('formats noon as 12 PM', () => {
            const result = formatAccessibleTime('12:00', 'en');
            expect(result).toBe('12 PM');
        });

        test('omits minutes when they are zero', () => {
            const result = formatAccessibleTime('09:00', 'en');
            expect(result).toBe('9 AM');
        });

        test('includes minutes when they are non-zero', () => {
            const result = formatAccessibleTime('09:15', 'en');
            expect(result).toBe('9:15 AM');
        });

        test('uses English format as default', () => {
            const result = formatAccessibleTime('14:30');
            expect(result).toBe('2:30 PM');
        });
    });

    describe('24-hour format (non-English locales)', () => {
        test('formats time in 24-hour format for Czech locale', () => {
            const result = formatAccessibleTime('14:30', 'cs');
            expect(result).toBe('14:30');
        });

        test('formats time in 24-hour format for Slovak locale', () => {
            const result = formatAccessibleTime('09:15', 'sk');
            expect(result).toBe('9:15');
        });

        test('formats time in 24-hour format for German locale', () => {
            const result = formatAccessibleTime('22:45', 'de');
            expect(result).toBe('22:45');
        });

        test('formats midnight correctly for non-English locale', () => {
            const result = formatAccessibleTime('00:00', 'cs');
            expect(result).toBe('0:00');
        });

        test('formats noon correctly for non-English locale', () => {
            const result = formatAccessibleTime('12:00', 'cs');
            expect(result).toBe('12:00');
        });
    });

    describe('edge cases', () => {
        test('handles single-digit hours', () => {
            const result = formatAccessibleTime('5:30', 'en');
            expect(result).toBe('5:30 AM');
        });

        test('handles end of day time', () => {
            const result = formatAccessibleTime('23:59', 'en');
            expect(result).toBe('11:59 PM');
        });

        test('handles time with leading zeros', () => {
            const result = formatAccessibleTime('01:05', 'en');
            expect(result).toBe('1:05 AM');
        });
    });
});
