import { Translate } from 'next-translate';
import { formatPrice } from 'utils/formaters/formatPrice';
import { describe, expect, test } from 'vitest';

const t = ((key: string) => key) as Translate;

describe('formatPrice', () => {
    test('returns the translated "Free" for zero price', () => {
        expect(formatPrice(0, 'EUR', t, 'en', 2)).toBe('Free');
    });

    test('returns formatted zero price when explicitZero option is set', () => {
        expect(formatPrice(0, 'EUR', t, 'en', 2, { explicitZero: true })).toBe('€0.00');
    });

    test('formats the price in the given currency with the given fraction digits', () => {
        expect(formatPrice(139.96, 'EUR', t, 'en', 2)).toBe('€139.96');
        expect(formatPrice(3499, 'CZK', t, 'en', 0)).toBe('CZK\u{a0}3,499');
    });

    test('falls back to the Intl default fraction digits when minimumFractionDigits is undefined', () => {
        expect(formatPrice(3499, 'CZK', t, 'en', undefined)).toBe('CZK\u{a0}3,499.00');
    });
});
