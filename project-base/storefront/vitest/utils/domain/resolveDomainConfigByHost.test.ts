import { resolveDomainConfigByHost } from 'utils/domain/domainConfig';
import { describe, expect, test, vi } from 'vitest';

vi.mock('envConfig', () => ({
    getPublicConfigProperty: (key: string) => {
        if (key === 'domains') {
            return [
                {
                    url: 'http://localhost:8000',
                    defaultLocale: 'en',
                    domainId: 1,
                },
                {
                    url: 'http://localhost:8000/cs',
                    defaultLocale: 'cs',
                    domainId: 2,
                },
                {
                    url: 'http://shop.example.com',
                    defaultLocale: 'en',
                    domainId: 3,
                },
            ];
        }
        if (key === 'cdnDomain') {
            return 'http://cdn.example.com';
        }
        return undefined;
    },
}));

describe('resolveDomainConfigByHost', () => {
    describe('default locale matching', () => {
        test('matches domain by host for default locale', () => {
            const result = resolveDomainConfigByHost('localhost:8000');

            expect(result.domainConfig?.domainId).toBe(1);
        });

        test('normalizes port 3000 to 8000', () => {
            const result = resolveDomainConfigByHost('localhost:3000');

            expect(result.domainConfig?.domainId).toBe(1);
        });

        test('matches domain without port', () => {
            const result = resolveDomainConfigByHost('shop.example.com');

            expect(result.domainConfig?.domainId).toBe(3);
        });
    });

    describe('non-default locale matching', () => {
        test('matches domain with locale path prefix', () => {
            const result = resolveDomainConfigByHost('localhost:8000', 'cs');

            expect(result.domainConfig?.domainId).toBe(2);
        });

        test('returns hostWithLocale with locale suffix', () => {
            const result = resolveDomainConfigByHost('localhost:8000', 'cs');

            expect(result.hostWithLocale).toBe('localhost:8000/cs');
        });
    });

    describe('no match', () => {
        test('returns undefined domainConfig when host does not match any domain', () => {
            const result = resolveDomainConfigByHost('unknown-host.com');

            expect(result.domainConfig).toBeUndefined();
        });

        test('still returns hostWithLocale when no match found', () => {
            const result = resolveDomainConfigByHost('unknown-host.com', 'cs');

            expect(result.hostWithLocale).toBe('unknown-host.com/cs');
        });
    });

    describe('CDN fallback', () => {
        test('matches first domain when request comes from CDN host', () => {
            const result = resolveDomainConfigByHost('cdn.example.com');

            expect(result.domainConfig?.domainId).toBe(1);
        });
    });

    describe('fallback for default locale with locale-suffixed domains', () => {
        test('falls back to host match when default locale has no exact path match', () => {
            const result = resolveDomainConfigByHost('localhost:8000', 'default');

            expect(result.domainConfig?.domainId).toBe(1);
        });
    });
});
