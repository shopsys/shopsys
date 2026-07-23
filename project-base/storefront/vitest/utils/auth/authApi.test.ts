import { NextApiRequest, NextApiResponse } from 'next';
import { handleAuthMutation, handleClearAuthCookies } from 'utils/auth/server/authApi';
import { beforeEach, describe, expect, test, vi } from 'vitest';

const { deleteCookieMock, fetchMock, getCookieMock, setCookieMock } = vi.hoisted(() => ({
    deleteCookieMock: vi.fn(),
    fetchMock: vi.fn(),
    getCookieMock: vi.fn(),
    setCookieMock: vi.fn(),
}));

vi.mock('cookies-next', () => ({
    deleteCookie: deleteCookieMock,
    getCookie: getCookieMock,
    setCookie: setCookieMock,
}));

vi.mock('envConfig', () => ({
    getPublicConfigProperty: () => [
        {
            currencyCode: 'EUR',
            defaultLocale: 'en',
            domainId: 1,
            fallbackTimezone: 'Europe/Prague',
            gtmId: '',
            isLuigisBoxActive: false,
            mapSetting: { latitude: 0, longitude: 0, zoom: 1 },
            packeteryCountry: 'cz',
            publicGraphqlEndpoint: 'https://store.example.com/graphql/',
            type: 'B2C',
            url: 'https://store.example.com/',
        },
    ],
    getServerConfigProperty: () => 'http://webserver:8080/',
}));

type ResponseMock = {
    endMock: ReturnType<typeof vi.fn>;
    jsonMock: ReturnType<typeof vi.fn>;
    response: NextApiResponse;
    statusMock: ReturnType<typeof vi.fn>;
};

const createResponse = (): ResponseMock => {
    const jsonMock = vi.fn();
    const endMock = vi.fn();
    const setHeaderMock = vi.fn();
    const response = {
        end: endMock,
        json: jsonMock,
        setHeader: setHeaderMock,
    } as unknown as NextApiResponse;
    const statusMock = vi.fn(() => response);
    response.status = statusMock;

    return { endMock, jsonMock, response, statusMock };
};

const createRequest = (
    operationName: string,
    variables: Record<string, unknown> = {},
    headers: NextApiRequest['headers'] = {},
): NextApiRequest => {
    return {
        body: { operationName, variables },
        headers: {
            host: 'store.example.com',
            origin: 'https://store.example.com',
            'x-domain-id': '1',
            'x-forwarded-proto': 'https',
            ...headers,
        },
        method: 'POST',
        socket: {
            remoteAddress: '127.0.0.1',
        },
    } as unknown as NextApiRequest;
};

describe('auth API', () => {
    beforeEach(() => {
        deleteCookieMock.mockClear();
        fetchMock.mockReset();
        getCookieMock.mockReset();
        setCookieMock.mockClear();
        vi.stubGlobal('fetch', fetchMock);
    });

    test('stores token response in cookies and redacts refresh token from browser response', async () => {
        fetchMock.mockResolvedValue(
            new Response(
                JSON.stringify({
                    data: {
                        Login: {
                            showCartMergeInfo: false,
                            tokens: { accessToken: 'new-access-token', refreshToken: 'new-refresh-token' },
                        },
                    },
                }),
                { status: 200 },
            ),
        );
        const { jsonMock, response, statusMock } = createResponse();

        await handleAuthMutation(createRequest('LoginMutation', { email: 'user@example.com' }), response);

        expect(statusMock).toHaveBeenCalledWith(200);
        expect(setCookieMock).toHaveBeenCalledWith(
            'refreshToken-1',
            'new-refresh-token',
            expect.objectContaining({ httpOnly: true, sameSite: 'lax', secure: true }),
        );
        expect(JSON.stringify(jsonMock.mock.calls[0][0])).not.toContain('new-refresh-token');
        expect(jsonMock.mock.calls[0][0]).toMatchObject({
            data: { Login: { tokens: { accessToken: 'new-access-token', refreshToken: '' } } },
        });
    });

    test('forwards validated client IP address to Frontend API', async () => {
        fetchMock.mockResolvedValue(
            new Response(
                JSON.stringify({
                    data: {
                        Login: null,
                    },
                }),
                { status: 200 },
            ),
        );
        const { response } = createResponse();

        await handleAuthMutation(
            createRequest('LoginMutation', {}, { 'x-forwarded-for': '203.0.113.99, 89.248.244.148' }),
            response,
        );

        const backendRequest = fetchMock.mock.calls[0][1] as RequestInit;
        expect(backendRequest.headers).toMatchObject({ 'X-Forwarded-For': '89.248.244.148' });
    });

    test('uses server-side refresh cookie instead of browser mutation variables', async () => {
        getCookieMock.mockImplementation((cookieName: string) => {
            if (cookieName === 'refreshToken-1') {
                return 'server-refresh-token';
            }

            return undefined;
        });
        fetchMock.mockResolvedValue(
            new Response(
                JSON.stringify({
                    data: {
                        RefreshTokens: { accessToken: 'new-access-token', refreshToken: 'rotated-refresh-token' },
                    },
                }),
                { status: 200 },
            ),
        );
        const { response } = createResponse();

        await handleAuthMutation(createRequest('RefreshTokens', { refreshToken: 'browser-refresh-token' }), response);

        const backendRequest = fetchMock.mock.calls[0][1] as RequestInit;
        expect(backendRequest.body).toContain('server-refresh-token');
        expect(backendRequest.body).not.toContain('browser-refresh-token');
    });

    test('does not forward an existing access token when refreshing tokens', async () => {
        getCookieMock.mockImplementation((cookieName: string) => {
            if (cookieName === 'accessToken-1') {
                return 'expired-access-token';
            }

            if (cookieName === 'refreshToken-1') {
                return 'server-refresh-token';
            }

            return undefined;
        });
        fetchMock.mockResolvedValue(
            new Response(
                JSON.stringify({
                    data: {
                        RefreshTokens: { accessToken: 'new-access-token', refreshToken: 'rotated-refresh-token' },
                    },
                }),
                { status: 200 },
            ),
        );
        const { response } = createResponse();

        await handleAuthMutation(createRequest('RefreshTokens', { refreshToken: '' }), response);

        const backendRequest = fetchMock.mock.calls[0][1] as RequestInit;
        expect(backendRequest.headers).not.toHaveProperty('X-Auth-Token');
    });

    test('rejects cross-origin requests before contacting Frontend API', async () => {
        const request = createRequest('LoginMutation');
        request.headers.origin = 'https://attacker.example.com';
        const { response, statusMock } = createResponse();

        await handleAuthMutation(request, response);

        expect(statusMock).toHaveBeenCalledWith(403);
        expect(fetchMock).not.toHaveBeenCalled();
    });

    test('clears all authentication cookies on the clear endpoint', () => {
        const { endMock, response, statusMock } = createResponse();

        handleClearAuthCookies(createRequest('ClearSession'), response);

        expect(deleteCookieMock).toHaveBeenCalledTimes(3);
        expect(deleteCookieMock).toHaveBeenCalledWith(
            'refreshToken-1',
            expect.objectContaining({ path: '/', sameSite: 'lax', secure: true }),
        );
        expect(statusMock).toHaveBeenCalledWith(204);
        expect(endMock).toHaveBeenCalledOnce();
    });
});
