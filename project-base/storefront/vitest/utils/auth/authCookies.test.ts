import { IncomingMessage, ServerResponse } from 'node:http';
import { deleteCookie, setCookie } from 'cookies-next/client';
import {
    getAccessTokenFromCookies,
    getRefreshTokenFromCookies,
    hasRefreshTokenInCookies,
} from 'utils/auth/getTokensFromCookies';
import { removeAccessTokenFromCookies, removeTokensFromCookies } from 'utils/auth/removeTokensFromCookies';
import { setTokensToCookies } from 'utils/auth/setTokensToCookies';
import { getCookiesStoreState } from 'utils/cookies/cookiesStore';
import { afterEach, describe, expect, test, vi } from 'vitest';
import { defaultTestDomainConfig } from 'vitest/helpers/mockPublicConfig';

const domain = defaultTestDomainConfig;

afterEach(() => {
    vi.unstubAllGlobals();
    for (const cookie of document.cookie.split(';')) {
        const cookieName = cookie.split('=')[0].trim();
        if (cookieName) {
            deleteCookie(cookieName, { path: '/' });
        }
    }
});

describe('authentication cookies with the real cookie libraries', () => {
    test('reads server headers synchronously and keeps domains isolated', () => {
        const context = {
            req: {
                headers: { cookie: 'accessToken-1=encoded%2Btoken; refreshToken-1=refresh; refreshTokenPresent-1=1' },
            } as IncomingMessage,
        };

        expect(getAccessTokenFromCookies(domain, context)).toBe('encoded+token');
        expect(getRefreshTokenFromCookies(domain, context)).toBe('refresh');
        expect(hasRefreshTokenInCookies(domain, context)).toBe(true);
        expect(getAccessTokenFromCookies({ ...domain, domainId: 2 }, context)).toBeUndefined();
    });

    test('writes all tokens before resolving and removes them from the same request', async () => {
        const headers = new Map<string, string | string[]>([['Set-Cookie', 'unrelated=keep']]);
        const context = {
            req: { headers: { cookie: '' }, cookies: {} } as unknown as IncomingMessage,
            res: {
                getHeader: (name: string) => headers.get(name),
                setHeader: (name: string, value: string | string[]) => headers.set(name, value),
            } as unknown as ServerResponse,
        };

        await setTokensToCookies('access', 'refresh', domain, context);

        expect(getAccessTokenFromCookies(domain, context)).toBe('access');
        expect(getRefreshTokenFromCookies(domain, context)).toBe('refresh');
        expect(hasRefreshTokenInCookies(domain, context)).toBe(true);
        const writtenCookies = headers.get('Set-Cookie') as string[];
        expect(writtenCookies).toHaveLength(4);
        expect(writtenCookies[0]).toBe('unrelated=keep');
        expect(writtenCookies.find((cookie) => cookie.startsWith('refreshToken-1='))).toContain('HttpOnly');
        expect(writtenCookies.find((cookie) => cookie.startsWith('refreshToken-1='))).toContain('Secure');
        expect(writtenCookies.find((cookie) => cookie.startsWith('refreshTokenPresent-1='))).not.toContain('HttpOnly');

        await removeTokensFromCookies(domain, context);

        expect(getAccessTokenFromCookies(domain, context)).toBeUndefined();
        expect(getRefreshTokenFromCookies(domain, context)).toBeUndefined();
        expect(hasRefreshTokenInCookies(domain, context)).toBe(false);
        expect((headers.get('Set-Cookie') as string[]).slice(-3)).toEqual([
            expect.stringContaining('accessToken-1=; Max-Age=-1'),
            expect.stringContaining('refreshToken-1=; Max-Age=-1'),
            expect.stringContaining('refreshTokenPresent-1=; Max-Age=-1'),
        ]);
    });

    test('reads and removes browser access tokens without deleting the refresh marker', () => {
        setCookie('accessToken-1', 'encoded+token', { path: '/' });
        setCookie('refreshTokenPresent-1', '1', { path: '/' });

        expect(getAccessTokenFromCookies(domain)).toBe('encoded+token');
        expect(hasRefreshTokenInCookies(domain)).toBe(true);

        removeAccessTokenFromCookies({ ...domain, url: 'http://localhost' });

        expect(getAccessTokenFromCookies(domain)).toBeUndefined();
        expect(hasRefreshTokenInCookies(domain)).toBe(true);
    });

    test('does not read browser credentials while rendering without a server request', () => {
        vi.stubGlobal('window', undefined);

        expect(getAccessTokenFromCookies(domain)).toBeUndefined();
        expect(hasRefreshTokenInCookies(domain)).toBe(false);
        expect(getCookiesStoreState(domain).productListViewMode).toBe('grid');
    });
});
