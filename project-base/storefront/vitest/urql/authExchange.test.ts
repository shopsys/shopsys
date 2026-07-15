import { AuthUtilities } from '@urql/exchange-auth';
import { parse } from 'graphql';
import { Operation } from 'urql';
import { getAuthExchangeOptions } from 'urql/authExchange';
import { beforeEach, describe, expect, test, vi } from 'vitest';

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
        removeTokensMock.mockClear();
        setTokensMock.mockClear();
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
});
