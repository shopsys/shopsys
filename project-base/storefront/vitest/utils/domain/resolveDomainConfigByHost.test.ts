import { getDomainConfig, resolveDomainConfigByHost } from 'utils/domain/domainConfig';
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
                {
                    url: 'https://secure.example.com',
                    defaultLocale: 'en',
                    domainId: 4,
                },
                {
                    url: 'http://plain.example.com',
                    defaultLocale: 'en',
                    domainId: 5,
                },
                {
                    url: 'https://cz.example.com',
                    defaultLocale: 'cs',
                    domainId: 6,
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

        test('normalizes https default port', () => {
            const result = resolveDomainConfigByHost('secure.example.com:443', 'default', 'https');

            expect(result.domainConfig?.domainId).toBe(4);
            expect(result.hostWithLocale).toBe('secure.example.com');
        });

        test('normalizes http default port', () => {
            const result = resolveDomainConfigByHost('plain.example.com:80', 'default', 'http');

            expect(result.domainConfig?.domainId).toBe(5);
            expect(result.hostWithLocale).toBe('plain.example.com');
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

        test('falls back to host match when locale is not configured for an existing host', () => {
            const result = resolveDomainConfigByHost('cz.example.com', 'en', 'https');

            expect(result.domainConfig?.domainId).toBe(6);
            expect(result.hostWithLocale).toBe('cz.example.com/en');
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

describe('getDomainConfig', () => {
    test('uses forwarded host before internal request host', () => {
        const result = getDomainConfig({
            locale: 'default',
            req: {
                headers: {
                    host: 'webserver-php-fpm:8080',
                    'x-forwarded-host': 'secure.example.com',
                    'x-forwarded-proto': 'https',
                },
            },
        } as any);

        expect(result.domainId).toBe(4);
    });

    test('uses the first forwarded host value', () => {
        const result = getDomainConfig({
            locale: 'default',
            req: {
                headers: {
                    host: 'webserver-php-fpm:8080',
                    'x-forwarded-host': 'secure.example.com, proxy.internal',
                    'x-forwarded-proto': 'https',
                },
            },
        } as any);

        expect(result.domainId).toBe(4);
    });

    test('uses the first forwarded host value from an array header', () => {
        const result = getDomainConfig({
            locale: 'default',
            req: {
                headers: {
                    host: 'webserver-php-fpm:8080',
                    'x-forwarded-host': ['secure.example.com', 'proxy.internal'],
                    'x-forwarded-proto': 'https',
                },
            },
        } as any);

        expect(result.domainId).toBe(4);
    });
});
