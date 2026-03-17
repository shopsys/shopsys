import { getClientDomainConfig } from 'utils/domain/getClientDomainConfig';
import { beforeEach, describe, expect, test, vi } from 'vitest';

const { mockDomains, mockCdnDomain } = vi.hoisted(() => ({
    mockDomains: [
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
            url: 'http://example.com',
            defaultLocale: 'en',
            domainId: 3,
        },
    ],
    mockCdnDomain: 'http://cdn.example.com',
}));

vi.mock('envConfig', () => ({
    getPublicConfigProperty: (key: string) => {
        if (key === 'domains') {
            return mockDomains;
        }
        if (key === 'cdnDomain') {
            return mockCdnDomain;
        }
        return undefined;
    },
}));

const setWindowLocation = (hostname: string, port = '') => {
    Object.defineProperty(window, 'location', {
        value: {
            host: port ? `${hostname}:${port}` : hostname,
            hostname,
            port,
        },
        writable: true,
    });
};

const setDocumentLang = (lang: string) => {
    Object.defineProperty(document.documentElement, 'lang', {
        value: lang,
        writable: true,
        configurable: true,
    });
};

describe('getClientDomainConfig', () => {
    beforeEach(() => {
        setDocumentLang('');
    });

    test('matches domain by locale and host (dev port normalization :3000 -> :8000)', () => {
        setWindowLocation('localhost', '3000');
        setDocumentLang('en');

        const result = getClientDomainConfig();
        expect(result.domainId).toBe(1);
    });

    test('matches domain with locale and path prefix', () => {
        setWindowLocation('localhost', '3000');
        setDocumentLang('cs');

        const result = getClientDomainConfig();
        expect(result.domainId).toBe(2);
    });

    test('matches domain without locale by host', () => {
        setWindowLocation('example.com');
        setDocumentLang('');

        const result = getClientDomainConfig();
        expect(result.domainId).toBe(3);
    });

    test('falls back to host-only match when locale does not match any domain', () => {
        setWindowLocation('localhost', '3000');
        setDocumentLang('de');

        const result = getClientDomainConfig();
        expect(result.domainId).toBe(1);
    });

    test('throws when no domain matches the host', () => {
        setWindowLocation('unknown-host.com');
        setDocumentLang('');

        expect(() => getClientDomainConfig()).toThrow(
            "Cannot resolve domainConfig on client for host 'unknown-host.com'",
        );
    });

    test('matches domain by exact host without port', () => {
        setWindowLocation('example.com');
        setDocumentLang('en');

        const result = getClientDomainConfig();
        expect(result.domainId).toBe(3);
    });

    test('matches domain with port 8000 directly (no normalization needed)', () => {
        setWindowLocation('localhost', '8000');
        setDocumentLang('en');

        const result = getClientDomainConfig();
        expect(result.domainId).toBe(1);
    });

    test('falls back to the first storefront domain on configured CDN host', () => {
        setWindowLocation('cdn.example.com');
        setDocumentLang('en');

        const result = getClientDomainConfig();
        expect(result.domainId).toBe(1);
    });
});
