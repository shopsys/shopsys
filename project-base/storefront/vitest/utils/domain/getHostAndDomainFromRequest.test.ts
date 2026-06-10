import { NextRequest } from 'next/server';
import { getHostAndDomainFromRequest } from 'utils/domain/getHostAndDomainFromRequest';
import { describe, expect, test, vi } from 'vitest';

vi.mock('config/staticRewritePaths', () => ({
    STATIC_REWRITE_PATHS: {
        'http://127.0.0.1:8000': {}, // Domain 1 - Root domain
        'http://127.0.0.1:8000/sk': {}, // Domain 2 - Locale in path
        'http://127.0.0.2:8000/sk': {}, // Domain 3 - Different host with locale in path
    },
}));

const createMockRequest = (host: string, path: string, forwardedHost?: string): NextRequest => {
    const url = `https://${forwardedHost ?? host}${path}`;
    const headers = new Headers({
        host,
        'x-forwarded-proto': 'https',
    });

    if (forwardedHost) {
        headers.set('x-forwarded-host', forwardedHost);
    }

    return {
        headers,
        url,
        nextUrl: { pathname: path },
    } as NextRequest;
};

describe('getHostAndDomainFromRequest', () => {
    describe('Basic domain resolution', () => {
        test('resolves root domain for base host and root path', () => {
            const request = createMockRequest('127.0.0.1:8000', '/');
            const result = getHostAndDomainFromRequest(request);

            expect(result).toEqual({
                host: 'http://127.0.0.1:8000/',
                domainId: 1,
                currentLocale: 'default',
            });
        });

        test('resolves domain by forwarded host before internal host header', () => {
            const request = createMockRequest('webserver-php-fpm:8080', '/products', '127.0.0.1:8000');
            const result = getHostAndDomainFromRequest(request);

            expect(result).toEqual({
                host: 'http://127.0.0.1:8000/',
                domainId: 1,
                currentLocale: 'default',
            });
        });

        test('resolves root domain for base host and any path', () => {
            const request = createMockRequest('127.0.0.1:8000', '/products');
            const result = getHostAndDomainFromRequest(request);

            expect(result).toEqual({
                host: 'http://127.0.0.1:8000/',
                domainId: 1,
                currentLocale: 'default',
            });
        });
    });

    describe('Locale-in-path domain resolution', () => {
        test('resolves exact locale path match', () => {
            const request = createMockRequest('127.0.0.1:8000', '/sk');
            const result = getHostAndDomainFromRequest(request);

            expect(result).toEqual({
                host: 'http://127.0.0.1:8000/sk/',
                domainId: 2,
                currentLocale: 'sk',
            });
        });

        test('resolves locale path with trailing slash', () => {
            const request = createMockRequest('127.0.0.1:8000', '/sk/');
            const result = getHostAndDomainFromRequest(request);

            expect(result).toEqual({
                host: 'http://127.0.0.1:8000/sk/',
                domainId: 2,
                currentLocale: 'sk',
            });
        });

        test('resolves subpath under locale prefix', () => {
            const request = createMockRequest('127.0.0.1:8000', '/sk/products');
            const result = getHostAndDomainFromRequest(request);

            expect(result).toEqual({
                host: 'http://127.0.0.1:8000/sk/',
                domainId: 2,
                currentLocale: 'sk',
            });
        });

        test('partial match should not resolve to locale domain', () => {
            // /skkk should NOT match /sk domain, should go to root domain
            const request = createMockRequest('127.0.0.1:8000', '/skkk');
            const result = getHostAndDomainFromRequest(request);

            expect(result).toEqual({
                host: 'http://127.0.0.1:8000/',
                domainId: 1,
                currentLocale: 'default',
            });
        });
    });

    describe('Browser locale-based fallback resolution', () => {
        test('redirects to locale domain based on Accept-Language when accessing base host without locale', () => {
            // Access http://127.0.0.2:8000/ (base host) when only http://127.0.0.2:8000/sk is configured
            // Should trigger fallback with browser locale detection
            const url = 'https://127.0.0.2:8000/';
            const headers = new Headers({
                host: '127.0.0.2:8000',
                'x-forwarded-proto': 'https',
                'accept-language': 'sk-SK,sk;q=0.9,en;q=0.8',
            });

            const request = {
                headers,
                url,
                nextUrl: { pathname: '/' },
            } as NextRequest;

            const result = getHostAndDomainFromRequest(request);

            // Should redirect to the /sk domain since browser prefers Slovak
            expect(result).toEqual({
                host: 'http://127.0.0.2:8000/sk/',
                domainId: 3,
                currentLocale: 'sk',
                redirect: true,
            });
        });

        test('redirects to first available domain when browser locale does not match', () => {
            // Access base host with a locale that doesn't match any domain
            const url = 'https://127.0.0.2:8000/';
            const headers = new Headers({
                host: '127.0.0.2:8000',
                'x-forwarded-proto': 'https',
                'accept-language': 'fr-FR,fr;q=0.9,en;q=0.8', // French preferred
            });

            const request = {
                headers,
                url,
                nextUrl: { pathname: '/' },
            } as NextRequest;

            const result = getHostAndDomainFromRequest(request);

            // Should fallback to the only available domain for this host (domain 3)
            expect(result).toEqual({
                host: 'http://127.0.0.2:8000/sk/',
                domainId: 3,
                currentLocale: 'sk',
                redirect: true,
            });
        });

        test('redirects to locale domain when accessing base path with trailing content', () => {
            // Access http://127.0.0.2:8000/products when only http://127.0.0.2:8000/sk is configured
            const url = 'https://127.0.0.2:8000/products';
            const headers = new Headers({
                host: '127.0.0.2:8000',
                'x-forwarded-proto': 'https',
                'accept-language': 'sk-SK,sk;q=0.9',
            });

            const request = {
                headers,
                url,
                nextUrl: { pathname: '/products' },
            } as NextRequest;

            const result = getHostAndDomainFromRequest(request);

            // Should redirect to the /sk domain
            expect(result).toEqual({
                host: 'http://127.0.0.2:8000/sk/',
                domainId: 3,
                currentLocale: 'sk',
                redirect: true,
            });
        });
    });
});
