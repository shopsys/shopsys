import { findDomainByPath, getDomainEntries } from 'utils/domain/domainMatcher';
import { describe, expect, test, vi } from 'vitest';

vi.mock('config/staticRewritePaths', () => ({
    STATIC_REWRITE_PATHS: {
        'http://127.0.0.1:8000': {},
        'http://127.0.0.1:8000/sk': {},
        'http://127.0.0.1:8000/cs': {},
        'http://127.0.0.2:8000/en': {},
    },
}));

vi.mock('utils/domain/domainUtils', () => ({
    getExplicitPathDomainLocaleOrDefault: vi.fn((url: string) => {
        if (url.includes('/sk')) {
            return 'sk';
        }
        if (url.includes('/cs')) {
            return 'cs';
        }
        if (url.includes('/en')) {
            return 'en';
        }
        return 'default';
    }),
}));

describe('getDomainEntries', () => {
    test('creates domain entries with pre-parsed URL components', () => {
        const entries = getDomainEntries();

        expect(entries).toHaveLength(4);

        // Verify first entry (root domain)
        expect(entries[0]).toEqual({
            domainUrl: 'http://127.0.0.1:8000',
            locale: 'default',
            domainId: 1,
            host: '127.0.0.1:8000',
            pathname: '/',
        });

        // Verify locale path entry
        expect(entries[1]).toEqual({
            domainUrl: 'http://127.0.0.1:8000/sk',
            locale: 'sk',
            domainId: 2,
            host: '127.0.0.1:8000',
            pathname: '/sk',
        });

        // Verify Czech locale entry
        expect(entries[2]).toEqual({
            domainUrl: 'http://127.0.0.1:8000/cs',
            locale: 'cs',
            domainId: 3,
            host: '127.0.0.1:8000',
            pathname: '/cs',
        });
    });

    test('handles trailing slashes correctly', () => {
        const entries = getDomainEntries();

        // All entries should have trailing slashes removed
        entries.forEach((entry) => {
            expect(entry.domainUrl).not.toMatch(/\/$/);
            // Root path is special case - it remains as '/'
            if (entry.pathname !== '/') {
                expect(entry.pathname).not.toMatch(/\/$/);
            }
        });
    });
});

describe('findDomainByPath', () => {
    const mockDomainEntries = getDomainEntries();

    describe('Host filtering', () => {
        test('filters domains by matching host', () => {
            const result = findDomainByPath(mockDomainEntries, 'http://127.0.0.2:8000', '/en/products');

            expect(result).toEqual(mockDomainEntries[3]); // Only domain with host 127.0.0.2:8000
        });

        test('returns undefined when no host matches', () => {
            const result = findDomainByPath(mockDomainEntries, 'http://nonexistent.com', '/any/path');

            expect(result).toBeUndefined();
        });
    });

    describe('Path length sorting and matching', () => {
        test('matches locale paths correctly', () => {
            // /sk/products should match /sk domain
            const result = findDomainByPath(mockDomainEntries, 'http://127.0.0.1:8000', '/sk/products');

            expect(result).toEqual(mockDomainEntries[1]); // /sk domain
        });

        test('matches root domain for unmatched paths', () => {
            const result = findDomainByPath(mockDomainEntries, 'http://127.0.0.1:8000', '/products');

            expect(result).toEqual(mockDomainEntries[0]); // Root domain
        });

        test('does not match locale domain for paths starting with locale code', () => {
            // /sk-any-string should NOT match /sk domain, should match root
            const result = findDomainByPath(mockDomainEntries, 'http://127.0.0.1:8000', '/sk-any-string');

            expect(result).toEqual(mockDomainEntries[0]); // Root domain, not /sk
        });
    });

    describe('Empty results', () => {
        test('returns undefined when no domains provided', () => {
            const result = findDomainByPath([], 'http://127.0.0.1:8000', '/any/path');

            expect(result).toBeUndefined();
        });

        test('returns undefined when no domains match host', () => {
            const result = findDomainByPath(mockDomainEntries, 'http://different-host.com', '/any/path');

            expect(result).toBeUndefined();
        });
    });
});
