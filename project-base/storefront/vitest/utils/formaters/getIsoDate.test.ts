import { getIsoDate } from 'utils/formaters/getIsoDate';
import { describe, expect, test } from 'vitest';

describe('getIsoDate test', () => {
    test('should return the calendar day of the date-time in the given timezone', () => {
        expect(getIsoDate('2026-07-30T22:00:00+00:00', 'Europe/Prague')).toBe('2026-07-31');
        expect(getIsoDate('2026-07-30T22:00:00+00:00', 'UTC')).toBe('2026-07-30');
        expect(getIsoDate('2026-07-30T22:00:00+00:00', 'America/New_York')).toBe('2026-07-30');
        expect(getIsoDate('2026-07-31T00:00:00+02:00', 'Europe/Prague')).toBe('2026-07-31');
    });

    test('should return the day of a plain ISO 8601 date in a timezone at or ahead of UTC as is', () => {
        expect(getIsoDate('2026-07-31', 'Europe/Prague')).toBe('2026-07-31');
    });

    test('should return undefined for a value that is not a date-time', () => {
        expect(getIsoDate('not-a-date', 'Europe/Prague')).toBeUndefined();
        expect(getIsoDate('', 'Europe/Prague')).toBeUndefined();
    });
});
