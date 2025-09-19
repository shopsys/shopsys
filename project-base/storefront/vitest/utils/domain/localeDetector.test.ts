import type { DomainEntry } from 'utils/domain/domainMatcher';
import { findDomainByBrowserLocale } from 'utils/domain/localeDetector';
import { describe, expect, test, vi } from 'vitest';

vi.mock('utils/domain/domainUtils', () => ({
    DEFAULT_LOCALE: 'default',
}));

describe('findDomainByBrowserLocale', () => {
    const mockDomainEntries: DomainEntry[] = [
        {
            domainUrl: 'http://127.0.0.1:8000',
            locale: 'default',
            domainId: 1,
            host: '127.0.0.1:8000',
            pathname: '/',
        },
        {
            domainUrl: 'http://127.0.0.1:8000/sk',
            locale: 'sk',
            domainId: 2,
            host: '127.0.0.1:8000',
            pathname: '/sk',
        },
        {
            domainUrl: 'http://127.0.0.1:8000/cs',
            locale: 'cs',
            domainId: 3,
            host: '127.0.0.1:8000',
            pathname: '/cs',
        },
    ];

    describe('Browser locale matching', () => {
        test('matches first browser preference that has corresponding domain', () => {
            const result = findDomainByBrowserLocale(mockDomainEntries, 'sk-SK,sk;q=0.9,cs;q=0.8,en;q=0.7');

            expect(result).toEqual(mockDomainEntries[1]); // Slovak domain
        });

        test('matches second preference when first is not available', () => {
            const result = findDomainByBrowserLocale(mockDomainEntries, 'de-DE,de;q=0.9,cs;q=0.8,en;q=0.7');

            expect(result).toEqual(mockDomainEntries[2]); // Czech domain (de not available)
        });

        test('skips DEFAULT_LOCALE domains during matching', () => {
            const result = findDomainByBrowserLocale(mockDomainEntries, 'default;q=1.0,sk;q=0.9');

            // Should match 'sk', not 'default' even though default has higher quality
            expect(result).toEqual(mockDomainEntries[1]);
        });

        test('is case insensitive', () => {
            const result = findDomainByBrowserLocale(mockDomainEntries, 'SK-SK,SK;q=0.9');

            expect(result).toEqual(mockDomainEntries[1]); // Should match 'sk' domain
        });
    });

    describe('Fallback behavior', () => {
        test('returns first domain when no browser locales match', () => {
            const result = findDomainByBrowserLocale(mockDomainEntries, 'fr-FR,fr;q=0.9,de;q=0.8');

            expect(result).toEqual(mockDomainEntries[0]); // First domain as fallback
        });

        test('returns first domain when accept-language is empty', () => {
            const result = findDomainByBrowserLocale(mockDomainEntries, '');

            expect(result).toEqual(mockDomainEntries[0]);
        });

        test('returns first domain when only DEFAULT_LOCALE domains are provided', () => {
            const defaultOnlyDomains: DomainEntry[] = [
                {
                    domainUrl: 'http://127.0.0.1:8000',
                    locale: 'default',
                    domainId: 1,
                    host: '127.0.0.1:8000',
                    pathname: '/',
                },
            ];

            const result = findDomainByBrowserLocale(defaultOnlyDomains, 'sk-SK,sk;q=0.9');

            expect(result).toEqual(defaultOnlyDomains[0]);
        });
    });

    describe('Accept-Language parsing edge cases', () => {
        test('handles quality values correctly', () => {
            // cs has higher quality than sk, but both are available
            const result = findDomainByBrowserLocale(mockDomainEntries, 'sk;q=0.7,cs;q=0.9');

            expect(result).toEqual(mockDomainEntries[2]); // Czech domain (higher quality)
        });

        test('handles missing quality values (defaults to 1.0)', () => {
            const result = findDomainByBrowserLocale(mockDomainEntries, 'sk,cs;q=0.9');

            expect(result).toEqual(mockDomainEntries[1]); // Slovak domain (quality 1.0 > 0.9)
        });

        test('extracts language code from full locale', () => {
            const result = findDomainByBrowserLocale(mockDomainEntries, 'sk-SK,cs-CZ;q=0.8');

            expect(result).toEqual(mockDomainEntries[1]); // Should match 'sk' from 'sk-SK'
        });

        test('removes duplicate locales', () => {
            const result = findDomainByBrowserLocale(mockDomainEntries, 'sk-SK,sk;q=0.9,sk-CZ;q=0.8');

            // All should resolve to 'sk', and first match should win
            expect(result).toEqual(mockDomainEntries[1]);
        });

        test('handles malformed accept-language gracefully', () => {
            const result = findDomainByBrowserLocale(mockDomainEntries, 'invalid-header;malformed');

            // Should fallback to first domain when parsing fails
            expect(result).toEqual(mockDomainEntries[0]);
        });

        test('handles whitespace and formatting variations', () => {
            const result = findDomainByBrowserLocale(mockDomainEntries, ' sk-SK , cs ; q=0.8 , en;q=0.7 ');

            expect(result).toEqual(mockDomainEntries[1]); // Should trim and parse correctly
        });
    });

    describe('Priority and ordering', () => {
        test('respects quality value ordering', () => {
            const result = findDomainByBrowserLocale(mockDomainEntries, 'en;q=0.9,cs;q=0.95,sk;q=0.8');

            expect(result).toEqual(mockDomainEntries[2]); // Czech (0.95) should win over Slovak (0.8)
        });

        test('stops at first match regardless of remaining preferences', () => {
            const result = findDomainByBrowserLocale(mockDomainEntries, 'sk;q=1.0,cs;q=0.9');

            // Should match Slovak and not continue checking Czech
            expect(result).toEqual(mockDomainEntries[1]);
        });
    });
});
