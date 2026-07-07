import { getDateTimeFromDateQuery, isDateQueryValid } from 'utils/parsing/getDateTimeFromDateQuery';
import { describe, expect, test } from 'vitest';

describe('getDateTimeFromDateQuery tests', () => {
    test('date from should be returned as UTC start of day in provided timezone', () => {
        expect(getDateTimeFromDateQuery('2026-06-11', false, 'Europe/Prague')).toBe('2026-06-10T22:00:00+00:00');
    });

    test('date to should be returned as UTC end of day in provided timezone', () => {
        expect(getDateTimeFromDateQuery('2026-06-11', true, 'Europe/Prague')).toBe('2026-06-11T21:59:59+00:00');
    });

    test('impossible date should be ignored', () => {
        expect(getDateTimeFromDateQuery('2026-02-31', false, 'Europe/Prague')).toBe(null);
    });

    test('valid leap day should be accepted', () => {
        expect(isDateQueryValid('2024-02-29')).toBe(true);
    });

    test('invalid leap day should be ignored', () => {
        expect(isDateQueryValid('2026-02-29')).toBe(false);
    });
});
