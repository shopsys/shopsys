import { AuthUtilities, authExchange } from '@urql/exchange-auth';
import { parse } from 'graphql';
import { RedisClientType } from 'redis';
import { Client, fetchExchange, Operation } from 'urql';
import { getAuthExchangeOptions } from 'urql/authExchange';
import { fetcher } from 'urql/fetcher';
import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest';

vi.mock('utils/isClient', () => ({ isClient: false }));

const {
    authMutationFetcherMock,
    getAccessTokenMock,
    getRefreshTokenMock,
    hasRefreshTokenMock,
    removeAccessTokenMock,
    removeTokensMock,
    setTokensMock,
} = vi.hoisted(() => ({
    authMutationFetcherMock: vi.fn(),
    getAccessTokenMock: vi.fn(),
    getRefreshTokenMock: vi.fn(),
    hasRefreshTokenMock: vi.fn(),
    removeAccessTokenMock: vi.fn(),
    removeTokensMock: vi.fn(),
    setTokensMock: vi.fn(),
}));

vi.mock('utils/auth/authMutationFetcher', () => ({
    clearAuthCookies: vi.fn(),
    getAuthMutationFetcher: () => authMutationFetcherMock,
}));

vi.mock('utils/auth/getTokensFromCookies', () => ({
    getAccessTokenFromCookies: getAccessTokenMock,
    getRefreshTokenFromCookies: getRefreshTokenMock,
    hasRefreshTokenInCookies: hasRefreshTokenMock,
}));

vi.mock('utils/auth/removeTokensFromCookies', () => ({
    removeAccessTokenFromCookies: removeAccessTokenMock,
    removeTokensFromCookies: removeTokensMock,
}));

vi.mock('utils/auth/setTokensToCookies', () => ({
    setTokensToCookies: setTokensMock,
}));

describe('getAuthExchangeOptions', () => {
    const domainConfig = { domainId: 1 } as never;

    beforeEach(() => {
        getAccessTokenMock.mockReset();
        getRefreshTokenMock.mockReset();
        hasRefreshTokenMock.mockReset();
        removeAccessTokenMock.mockClear();
        removeTokensMock.mockReset();
        setTokensMock.mockReset();
    });

    afterEach(() => {
        vi.unstubAllGlobals();
    });

    test('adds access token even though refresh token is hidden from browser JavaScript', async () => {
        getAccessTokenMock.mockReturnValue('access-token');
        const config = await getAuthExchangeOptions(domainConfig)({} as AuthUtilities);
        const operation = {
            context: { fetchOptions: { headers: { Existing: 'header' } } },
            kind: 'query',
            query: parse('query CurrentCustomer { currentCustomerUser { uuid } }'),
        } as unknown as Operation;

        const authorizedOperation = config.addAuthToOperation(operation);
        const fetchOptions = authorizedOperation.context.fetchOptions as RequestInit;

        expect(fetchOptions.headers).toMatchObject({
            Existing: 'header',
            'X-Auth-Token': 'Bearer access-token',
        });
    });

    test('refreshes browser session through auth endpoint without exposing refresh token', async () => {
        hasRefreshTokenMock.mockReturnValue(true);
        const mutateMock = vi.fn().mockResolvedValue({
            data: { RefreshTokens: { accessToken: 'new-access-token', refreshToken: '' } },
        });
        const config = await getAuthExchangeOptions(domainConfig)({ mutate: mutateMock } as unknown as AuthUtilities);

        await config.refreshAuth();

        expect(mutateMock).toHaveBeenCalledWith(
            expect.anything(),
            { refreshToken: '' },
            { fetch: authMutationFetcherMock },
        );
        expect(setTokensMock).not.toHaveBeenCalled();
    });

    test('refreshes SSR session directly and writes rotated server cookies', async () => {
        const context = { req: {}, res: {} } as never;
        getRefreshTokenMock.mockReturnValue('server-refresh-token');
        const mutateMock = vi.fn().mockResolvedValue({
            data: { RefreshTokens: { accessToken: 'new-access-token', refreshToken: 'new-refresh-token' } },
        });
        const config = await getAuthExchangeOptions(
            domainConfig,
            context,
        )({
            mutate: mutateMock,
        } as unknown as AuthUtilities);

        await config.refreshAuth();

        expect(mutateMock).toHaveBeenCalledWith(expect.anything(), { refreshToken: 'server-refresh-token' }, undefined);
        expect(setTokensMock).toHaveBeenCalledWith('new-access-token', 'new-refresh-token', domainConfig, context);
    });

    test.each([
        false,
        true,
    ])('recovers from a malformed SSR access token without sending it to the backend (refresh token: %s)', async (hasRefreshToken) => {
        vi.stubGlobal('window', undefined);
        const context = { req: {}, res: {} } as never;
        const refreshedAccessToken = 'eyJhbGciOiJSUzI1NiJ9.eyJleHAiOjQxMDI0NDQ4MDB9.signature';
        const anonymousData = { promotedProducts: [{ uuid: 'anonymous-product' }] };
        const authenticatedData = { promotedProducts: [{ uuid: 'authenticated-product' }] };
        getAccessTokenMock.mockReturnValue('x');
        getRefreshTokenMock.mockReturnValue(hasRefreshToken ? 'server-refresh-token' : undefined);
        removeTokensMock.mockImplementation(() => getAccessTokenMock.mockReturnValue(undefined));
        setTokensMock.mockImplementation((accessToken) => getAccessTokenMock.mockReturnValue(accessToken));
        const backendFetch = vi.fn<typeof fetch>(async (_input, init) => {
            if (new Headers(init?.headers).get('X-Auth-Token') === 'Bearer x') {
                return Response.json({
                    errors: [{ message: 'Token is not valid.', extensions: { userCode: 'invalid-token' } }],
                });
            }

            if (JSON.parse(init?.body as string).query.includes('mutation RefreshTokens')) {
                return Response.json({
                    data: {
                        RefreshTokens: {
                            accessToken: refreshedAccessToken,
                            refreshToken: 'rotated-refresh-token',
                        },
                    },
                });
            }

            return Response.json({ data: authenticatedData });
        });
        vi.stubGlobal('fetch', backendFetch);
        const redisClient = {
            get: vi.fn().mockResolvedValue(JSON.stringify(anonymousData)),
            set: vi.fn(),
        };
        const createTestClient = () =>
            new Client({
                url: 'https://test.ts/graphql/',
                preferGetMethod: false,
                exchanges: [authExchange(getAuthExchangeOptions(domainConfig, context)), fetchExchange],
                fetch: fetcher(redisClient as unknown as RedisClientType),
            });
        const query = parse(
            'query PromotedProductsQuery @redisCachePerPricingGroup(ttl: 3600) { promotedProducts { uuid } }',
        );

        const result = await createTestClient().query(query, {}).toPromise();

        expect(redisClient.set).not.toHaveBeenCalled();
        if (hasRefreshToken) {
            expect(result.error).toBeUndefined();
            expect(result.data).toEqual(authenticatedData);
            expect(backendFetch).toHaveBeenCalledTimes(2);
            expect(new Headers(backendFetch.mock.calls[0][1]?.headers).has('X-Auth-Token')).toBe(false);
            expect(JSON.parse(backendFetch.mock.calls[0][1]?.body as string).variables).toEqual({
                refreshToken: 'server-refresh-token',
            });
            expect(new Headers(backendFetch.mock.calls[1][1]?.headers).get('X-Auth-Token')).toBe(
                `Bearer ${refreshedAccessToken}`,
            );
            expect(setTokensMock).toHaveBeenCalledWith(
                refreshedAccessToken,
                'rotated-refresh-token',
                domainConfig,
                context,
            );
            expect(removeTokensMock).not.toHaveBeenCalled();
            expect(redisClient.get).not.toHaveBeenCalled();
        } else {
            expect(result.error?.graphQLErrors[0].extensions.userCode).toBe('invalid-token');
            expect(removeTokensMock).toHaveBeenCalledExactlyOnceWith(domainConfig, context);
            expect(setTokensMock).not.toHaveBeenCalled();
            expect(backendFetch).not.toHaveBeenCalled();

            const nextRequestResult = await createTestClient().query(query, {}).toPromise();

            expect(nextRequestResult.error).toBeUndefined();
            expect(nextRequestResult.data).toEqual(anonymousData);
            expect(backendFetch).not.toHaveBeenCalled();
            expect(redisClient.get).toHaveBeenCalledTimes(1);
        }
    });
});
