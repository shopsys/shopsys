import { IncomingMessage, ServerResponse } from 'node:http';
import { setCookie } from 'cookies-next';
import { setTokensToCookies } from 'utils/auth/setTokensToCookies';
import { beforeEach, describe, expect, test, vi } from 'vitest';

vi.mock('cookies-next', () => ({
    setCookie: vi.fn(),
}));

describe('setTokensToCookies', () => {
    const domainConfig = { domainId: 3, url: 'https://example.com' } as never;
    const context = {
        req: {} as IncomingMessage,
        res: {} as ServerResponse,
    };

    beforeEach(() => {
        vi.mocked(setCookie).mockClear();
    });

    test('stores refresh token in a secure HttpOnly SameSite=Lax cookie', () => {
        setTokensToCookies('access-token', 'refresh-token', domainConfig, context);

        expect(setCookie).toHaveBeenCalledWith(
            'refreshToken-3',
            'refresh-token',
            expect.objectContaining({
                httpOnly: true,
                maxAge: 1_209_600,
                path: '/',
                sameSite: 'lax',
                secure: true,
            }),
        );
    });

    test('stores only a non-sensitive refresh-token marker for browser JavaScript', () => {
        setTokensToCookies('access-token', 'refresh-token', domainConfig, context);

        expect(setCookie).toHaveBeenCalledWith(
            'refreshTokenPresent-3',
            '1',
            expect.objectContaining({
                httpOnly: false,
                maxAge: 1_209_600,
                path: '/',
                sameSite: 'lax',
                secure: true,
            }),
        );
    });
});
