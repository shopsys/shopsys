import { AuthUtilities, authExchange } from '@urql/exchange-auth';
import { parse } from 'graphql';
import { RedisClientType } from 'redis';
import { Client, CombinedError, fetchExchange, Operation } from 'urql';
import { getAuthExchangeOptions } from 'urql/authExchange';
import { getErrorExchange } from 'urql/errorExchange';
import { fetcher } from 'urql/fetcher';
import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest';

vi.mock('utils/isClient', () => ({ isClient: false }));
vi.mock('utils/errors/logException', () => ({ logException: vi.fn() }));

const {
    authMutationFetcherMock,
    clearAuthCookiesMock,
    getAccessTokenMock,
    getRefreshTokenMock,
    hasRefreshTokenMock,
    removeAccessTokenMock,
    removeTokensMock,
    setTokensMock,
} = vi.hoisted(() => ({
    authMutationFetcherMock: vi.fn(),
    clearAuthCookiesMock: vi.fn(),
    getAccessTokenMock: vi.fn(),
    getRefreshTokenMock: vi.fn(),
    hasRefreshTokenMock: vi.fn(),
    removeAccessTokenMock: vi.fn(),
    removeTokensMock: vi.fn(),
    setTokensMock: vi.fn(),
}));

vi.mock('utils/auth/authMutationFetcher', () => ({
    clearAuthCookies: clearAuthCookiesMock,
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

const createLockManager = () => {
    const queues = new Map<string, Promise<unknown>>();

    return {
        request: vi.fn((name: string, _options: LockOptions, callback: LockGrantedCallback<unknown>) => {
            const result = (queues.get(name) ?? Promise.resolve()).then(() => callback({ name, mode: 'exclusive' }));
            queues.set(
                name,
                result.catch(() => undefined),
            );

            return result;
        }),
    };
};

describe('getAuthExchangeOptions', () => {
    const domainConfig = { domainId: 1 } as never;

    beforeEach(() => {
        getAccessTokenMock.mockReset();
        authMutationFetcherMock.mockReset();
        clearAuthCookiesMock.mockReset();
        getRefreshTokenMock.mockReset();
        hasRefreshTokenMock.mockReset();
        removeAccessTokenMock.mockClear();
        removeTokensMock.mockReset();
        setTokensMock.mockReset();
    });

    afterEach(() => {
        vi.unstubAllGlobals();
        vi.useRealTimers();
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
            { fetch: expect.any(Function) },
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
        undefined,
        'expired-access-token',
    ])('concurrent tabs reuse the rotated cookie and authorize their next operation (initial token: %s)', async (initialAccessToken) => {
        vi.stubGlobal('navigator', { locks: createLockManager() });
        hasRefreshTokenMock.mockReturnValue(true);
        getAccessTokenMock.mockReturnValue(initialAccessToken);
        let finishRefresh: () => void = () => {};
        const response = new Promise<void>((resolve) => {
            finishRefresh = resolve;
        });
        const mutateMock = vi.fn(async () => {
            await response;
            getAccessTokenMock.mockReturnValue('rotated-access-token');

            return { data: { RefreshTokens: { accessToken: 'rotated-access-token', refreshToken: '' } } };
        });
        const tabs = await Promise.all(
            Array.from({ length: 3 }, () =>
                getAuthExchangeOptions(domainConfig)({ mutate: mutateMock } as unknown as AuthUtilities),
            ),
        );

        const refreshes = tabs.map((tab) => tab.refreshAuth());
        await vi.waitFor(() => expect(mutateMock).toHaveBeenCalled());
        finishRefresh();
        await Promise.all(refreshes);

        expect(mutateMock).toHaveBeenCalledTimes(1);
        const operation = {
            context: {},
            kind: 'query',
            query: parse('query CurrentCustomer { currentCustomerUser { uuid } }'),
        } as unknown as Operation;
        for (const tab of tabs) {
            const fetchOptions = tab.addAuthToOperation(operation).context.fetchOptions as RequestInit;
            expect(new Headers(fetchOptions.headers).get('X-Auth-Token')).toBe('Bearer rotated-access-token');
        }
        expect(removeAccessTokenMock).not.toHaveBeenCalled();
    });

    test('refreshes an unchanged token even when it is still present in the cookie', async () => {
        vi.stubGlobal('navigator', { locks: createLockManager() });
        hasRefreshTokenMock.mockReturnValue(true);
        getAccessTokenMock.mockReturnValue('rejected-access-token');
        const mutateMock = vi.fn().mockResolvedValue({ data: { RefreshTokens: { accessToken: 'new-token' } } });
        const config = await getAuthExchangeOptions(domainConfig)({ mutate: mutateMock } as unknown as AuthUtilities);

        await config.refreshAuth();

        expect(mutateMock).toHaveBeenCalledTimes(1);
    });

    test.each([
        'unavailable',
        'rejected',
        'throwing',
    ])('refreshes without a browser lock when the API is %s', async (availability) => {
        const request = vi.fn(() => {
            if (availability === 'throwing') {
                throw new Error('Lock API unavailable');
            }

            return Promise.reject(new Error('Lock API unavailable'));
        });
        vi.stubGlobal('navigator', availability === 'unavailable' ? {} : { locks: { request } });
        hasRefreshTokenMock.mockReturnValue(true);
        const mutateMock = vi.fn().mockResolvedValue({ data: { RefreshTokens: { accessToken: 'new-token' } } });
        const config = await getAuthExchangeOptions(domainConfig)({ mutate: mutateMock } as unknown as AuthUtilities);

        await config.refreshAuth();

        expect(mutateMock).toHaveBeenCalledTimes(1);
        expect(removeAccessTokenMock).not.toHaveBeenCalled();
    });

    test('a rejected refresh releases the lock so another tab can recover without logging out', async () => {
        vi.stubGlobal('navigator', { locks: createLockManager() });
        const error = new Error('Network unavailable');
        const consoleError = vi.spyOn(console, 'error').mockImplementation(() => {});
        hasRefreshTokenMock.mockReturnValue(true);
        const mutateMock = vi
            .fn()
            .mockRejectedValueOnce(error)
            .mockResolvedValueOnce({ data: { RefreshTokens: { accessToken: 'new-token' } } });
        const first = await getAuthExchangeOptions(domainConfig)({ mutate: mutateMock } as unknown as AuthUtilities);
        const second = await getAuthExchangeOptions(domainConfig)({ mutate: mutateMock } as unknown as AuthUtilities);

        const results = await Promise.allSettled([first.refreshAuth(), second.refreshAuth()]);

        expect(results.map((result) => result.status)).toEqual(['rejected', 'fulfilled']);
        expect(mutateMock).toHaveBeenCalledTimes(2);
        expect(consoleError).toHaveBeenCalledExactlyOnceWith(error);
        expect(removeAccessTokenMock).not.toHaveBeenCalled();
    });

    test('waiting tabs recheck the refresh cookie after another tab is logged out', async () => {
        vi.stubGlobal('navigator', { locks: createLockManager() });
        const reload = vi.fn();
        vi.stubGlobal('window', { location: { reload } });
        hasRefreshTokenMock.mockReturnValue(true);
        getAccessTokenMock.mockReturnValue('expired-token');
        const mutateMock = vi.fn(async () => {
            hasRefreshTokenMock.mockReturnValue(false);
            getAccessTokenMock.mockReturnValue(undefined);

            return { data: null };
        });
        const first = await getAuthExchangeOptions(domainConfig)({ mutate: mutateMock } as unknown as AuthUtilities);
        const second = await getAuthExchangeOptions(domainConfig)({ mutate: mutateMock } as unknown as AuthUtilities);

        await Promise.all([first.refreshAuth(), second.refreshAuth()]);

        expect(mutateMock).toHaveBeenCalledTimes(1);
        expect(reload).toHaveBeenCalledTimes(2);
        expect(clearAuthCookiesMock).toHaveBeenCalledExactlyOnceWith(domainConfig, expect.any(AbortSignal));
    });

    test('different domains can refresh independently on the same origin', async () => {
        vi.stubGlobal('navigator', { locks: createLockManager() });
        hasRefreshTokenMock.mockReturnValue(true);
        const mutateMock = vi.fn(() => new Promise(() => {}));
        const first = await getAuthExchangeOptions(domainConfig)({ mutate: mutateMock } as unknown as AuthUtilities);
        const second = await getAuthExchangeOptions({ domainId: 2 } as never)({
            mutate: mutateMock,
        } as unknown as AuthUtilities);
        vi.useFakeTimers();
        vi.spyOn(console, 'error').mockImplementation(() => {});

        const refreshes = Promise.allSettled([first.refreshAuth(), second.refreshAuth()]);
        await vi.advanceTimersByTimeAsync(0);
        expect(mutateMock).toHaveBeenCalledTimes(2);
        await vi.runAllTimersAsync();
        expect((await refreshes).map((result) => result.status)).toEqual(['rejected', 'rejected']);
    });

    test('a suspended lock holder cannot keep another tab waiting forever', async () => {
        vi.useFakeTimers();
        let lockSignal: AbortSignal | undefined;
        vi.stubGlobal('navigator', {
            locks: {
                request: vi.fn(
                    (_name, { signal }) =>
                        new Promise((_resolve, reject) => {
                            lockSignal = signal;
                            signal.addEventListener('abort', () => reject(signal.reason), { once: true });
                        }),
                ),
            },
        });
        hasRefreshTokenMock.mockReturnValue(true);
        const mutateMock = vi.fn().mockResolvedValue({ data: { RefreshTokens: { accessToken: 'new-token' } } });
        const config = await getAuthExchangeOptions(domainConfig)({ mutate: mutateMock } as unknown as AuthUtilities);

        const refresh = config.refreshAuth();
        expect(mutateMock).not.toHaveBeenCalled();
        await vi.advanceTimersByTimeAsync(10_000);
        await refresh;

        expect(lockSignal?.aborted).toBe(true);
        expect(mutateMock).toHaveBeenCalledTimes(1);
    });

    test('a hanging refresh is aborted and releases its lock without retrying or clearing newer cookies', async () => {
        vi.useFakeTimers();
        vi.stubGlobal('navigator', { locks: createLockManager() });
        const consoleError = vi.spyOn(console, 'error').mockImplementation(() => {});
        hasRefreshTokenMock.mockReturnValue(true);
        let requestSignal: AbortSignal | undefined;
        let finishRefresh: (value: unknown) => void = () => {};
        authMutationFetcherMock.mockImplementation((_input, init) => {
            requestSignal = init.signal;

            return new Promise((resolve) => {
                finishRefresh = resolve;
            });
        });
        const mutateMock = vi.fn(async (_document, _variables, { fetch: authFetch }) => authFetch('/graphql', {}));
        const config = await getAuthExchangeOptions(domainConfig)({ mutate: mutateMock } as unknown as AuthUtilities);

        const refresh = config.refreshAuth();
        const rejectedRefresh = expect(refresh).rejects.toThrow('Authentication refresh timed out');
        await vi.advanceTimersByTimeAsync(10_000);
        await rejectedRefresh;
        finishRefresh({ data: null });
        await vi.advanceTimersByTimeAsync(0);

        expect(requestSignal?.aborted).toBe(true);
        expect(consoleError).toHaveBeenCalledExactlyOnceWith(
            expect.objectContaining({ message: 'Authentication refresh timed out' }),
        );
        expect(mutateMock).toHaveBeenCalledTimes(1);
        expect(removeAccessTokenMock).not.toHaveBeenCalled();
        expect(vi.getTimerCount()).toBe(0);
    });

    test('SSR refresh never acquires browser locks', async () => {
        const locks = createLockManager();
        vi.stubGlobal('navigator', { locks });
        getRefreshTokenMock.mockReturnValue('server-refresh-token');
        const mutateMock = vi.fn().mockResolvedValue({ data: { RefreshTokens: { accessToken: 'new-token' } } });
        const config = await getAuthExchangeOptions(domainConfig, { req: {}, res: {} } as never)({
            mutate: mutateMock,
        } as unknown as AuthUtilities);

        await config.refreshAuth();

        expect(locks.request).not.toHaveBeenCalled();
        expect(mutateMock).toHaveBeenCalledTimes(1);
    });

    test('a late auth error reuses the cookie rotated while another tab still holds the lock', async () => {
        vi.stubGlobal('navigator', { locks: createLockManager() });
        hasRefreshTokenMock.mockReturnValue(true);
        getAccessTokenMock.mockReturnValue('expired-token');
        let finishRefresh: () => void = () => {};
        const response = new Promise<void>((resolve) => {
            finishRefresh = resolve;
        });
        const mutateMock = vi.fn(async () => {
            getAccessTokenMock.mockReturnValue('rotated-token');
            await response;

            return { data: { RefreshTokens: { accessToken: 'rotated-token' } } };
        });
        const first = await getAuthExchangeOptions(domainConfig)({ mutate: mutateMock } as unknown as AuthUtilities);
        const second = await getAuthExchangeOptions(domainConfig)({ mutate: mutateMock } as unknown as AuthUtilities);
        const operation = second.addAuthToOperation({
            context: {},
            kind: 'query',
            query: parse('query CurrentCustomer { currentCustomerUser { uuid } }'),
        } as unknown as Operation);

        const firstRefresh = first.refreshAuth();
        await vi.waitFor(() => expect(mutateMock).toHaveBeenCalledTimes(1));
        expect(
            second.didAuthError(
                new CombinedError({
                    graphQLErrors: [{ message: 'Token invalid', extensions: { userCode: 'invalid-token' } }],
                }),
                operation,
            ),
        ).toBe(true);
        const secondRefresh = second.refreshAuth();
        finishRefresh();
        await Promise.all([firstRefresh, secondRefresh]);

        expect(mutateMock).toHaveBeenCalledTimes(1);
        expect(removeAccessTokenMock).not.toHaveBeenCalled();
    });

    test.each([
        'timeout',
        'network error',
    ])('URQL reports a refresh %s without retrying a rejected token or clearing the session', async (failure) => {
        vi.useFakeTimers();
        vi.stubGlobal('navigator', { locks: createLockManager() });
        vi.spyOn(console, 'error').mockImplementation(() => {});
        hasRefreshTokenMock.mockReturnValue(true);
        getAccessTokenMock.mockReturnValue('expired-token');
        const backendFetch = vi.fn().mockResolvedValue(
            Response.json({
                errors: [{ message: 'Token invalid', extensions: { userCode: 'invalid-token' } }],
            }),
        );
        authMutationFetcherMock.mockImplementation(() =>
            failure === 'timeout' ? new Promise(() => {}) : Promise.reject(new Error('Network unavailable')),
        );
        const client = new Client({
            url: 'https://test.ts/graphql/',
            exchanges: [
                getErrorExchange(vi.fn() as never, domainConfig),
                authExchange(getAuthExchangeOptions(domainConfig)),
                fetchExchange,
            ],
            fetch: backendFetch,
        });

        const response = client.query(parse('query CurrentCustomer { currentCustomerUser { uuid } }'), {}).toPromise();
        await vi.advanceTimersByTimeAsync(10_001);
        const result = await response;

        expect(result.error?.networkError?.message).toBe(
            failure === 'timeout' ? 'Authentication refresh timed out' : 'Network unavailable',
        );
        expect(backendFetch).toHaveBeenCalledTimes(1);
        expect(authMutationFetcherMock).toHaveBeenCalledTimes(1);
        expect(removeAccessTokenMock).not.toHaveBeenCalled();
        expect(clearAuthCookiesMock).not.toHaveBeenCalled();
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
            isReady: true,
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
