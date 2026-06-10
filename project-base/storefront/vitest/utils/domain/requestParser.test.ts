import { NextRequest } from 'next/server';
import { parseRequest } from 'utils/domain/requestParser';
import { describe, expect, test } from 'vitest';

const createMockRequest = (
    host: string | null,
    url: string,
    xForwardedProto?: string,
    acceptLanguage?: string,
    xForwardedHost?: string,
): NextRequest => {
    const headers = new Headers();

    if (host) {
        headers.set('host', host);
    }

    if (xForwardedProto) {
        headers.set('x-forwarded-proto', xForwardedProto);
    }

    if (acceptLanguage) {
        headers.set('accept-language', acceptLanguage);
    }

    if (xForwardedHost) {
        headers.set('x-forwarded-host', xForwardedHost);
    }

    return {
        headers,
        url,
    } as NextRequest;
};

describe('parseRequest', () => {
    describe('Host header handling', () => {
        test('extracts host and builds base URL correctly', () => {
            const request = createMockRequest('127.0.0.1:8000', 'https://127.0.0.1:8000/products/123', 'https');

            const result = parseRequest(request);

            expect(result.requestBaseUrl).toBe('https://127.0.0.1:8000');
        });

        test('prefers forwarded host before internal host header', () => {
            const request = createMockRequest(
                'webserver-php-fpm:8080',
                'https://secure.example.com/products/123',
                'https',
                undefined,
                'secure.example.com',
            );

            const result = parseRequest(request);

            expect(result.requestBaseUrl).toBe('https://secure.example.com');
        });

        test('uses the first forwarded host value', () => {
            const request = createMockRequest(
                'webserver-php-fpm:8080',
                'https://secure.example.com/products/123',
                'https',
                undefined,
                'secure.example.com, proxy.internal',
            );

            const result = parseRequest(request);

            expect(result.requestBaseUrl).toBe('https://secure.example.com');
        });

        test('throws error when host header is missing', () => {
            const request = createMockRequest(null, 'https://example.com/path');

            expect(() => parseRequest(request)).toThrow('Host was not found in the request header.');
        });
    });

    describe('Protocol handling', () => {
        test('uses x-forwarded-proto header when present', () => {
            const request = createMockRequest('example.com', 'http://example.com/path', 'https');

            const result = parseRequest(request);

            expect(result.requestBaseUrl).toBe('https://example.com');
        });

        test('uses request URL protocol when x-forwarded-proto header is missing', () => {
            const request = createMockRequest('example.com', 'https://example.com/path');

            const result = parseRequest(request);

            expect(result.requestBaseUrl).toBe('https://example.com');
        });

        test('uses the first forwarded proto value', () => {
            const request = createMockRequest('example.com', 'http://example.com/path', 'https, http');

            const result = parseRequest(request);

            expect(result.requestBaseUrl).toBe('https://example.com');
        });
    });

    describe('Path extraction', () => {
        test('extracts pathname from original URL', () => {
            const request = createMockRequest('example.com', 'https://example.com/products/123?param=value#hash');

            const result = parseRequest(request);

            expect(result.requestPath).toBe('/products/123');
        });

        test('handles root path correctly', () => {
            const request = createMockRequest('example.com', 'https://example.com/');

            const result = parseRequest(request);

            expect(result.requestPath).toBe('/');
        });

        test('handles complex paths with special characters', () => {
            const request = createMockRequest('example.com', 'https://example.com/sk/products/test-product');

            const result = parseRequest(request);

            expect(result.requestPath).toBe('/sk/products/test-product');
        });
    });

    describe('Accept-Language header handling', () => {
        test('extracts accept-language header when present', () => {
            const request = createMockRequest(
                'example.com',
                'https://example.com/',
                'https',
                'sk-SK,sk;q=0.9,en;q=0.8',
            );

            const result = parseRequest(request);

            expect(result.acceptLanguage).toBe('sk-SK,sk;q=0.9,en;q=0.8');
        });

        test('returns empty string when accept-language header is missing', () => {
            const request = createMockRequest('example.com', 'https://example.com/', 'https');

            const result = parseRequest(request);

            expect(result.acceptLanguage).toBe('');
        });
    });

    describe('Complete parsing', () => {
        test('returns all parsed values correctly', () => {
            const request = createMockRequest(
                '127.0.0.1:3000',
                'https://127.0.0.1:3000/sk/products/test?id=123',
                'https',
                'cs-CZ,cs;q=0.9,en;q=0.8',
            );

            const result = parseRequest(request);

            expect(result).toEqual({
                requestBaseUrl: 'https://127.0.0.1:3000',
                requestPath: '/sk/products/test',
                acceptLanguage: 'cs-CZ,cs;q=0.9,en;q=0.8',
            });
        });
    });
});
