import { act, cleanup, renderHook, waitFor } from '@testing-library/react';
import { authExchange } from '@urql/exchange-auth';
import { deleteCookie, getCookie, setCookie } from 'cookies-next';
import { parse } from 'graphql';
import { PropsWithChildren } from 'react';
import { Client, fetchExchange, Provider } from 'urql';
import { getAuthExchangeOptions } from 'urql/authExchange';
import { useLogout } from 'utils/auth/useLogout';
import { afterEach, describe, expect, test, vi } from 'vitest';

vi.mock('components/providers/DomainConfigProvider', () => ({
    useDomainConfig: () => ({ domainId: 1 }),
}));

vi.mock('store/usePersistStore', () => ({
    usePersistStore: (selector: (state: unknown) => unknown) =>
        selector({ resetContactInformation: vi.fn(), updateProductListUuids: vi.fn() }),
}));

vi.mock('utils/auth/performAuthHardNavigation', () => ({ performAuthHardNavigation: vi.fn() }));
vi.mock('utils/auth/authNotificationStorage', () => ({ storeAuthNotification: vi.fn() }));
vi.mock('utils/order/orderConfirmationContextStorage', () => ({ clearOrderConfirmationContext: vi.fn() }));
vi.mock('utils/useBroadcastChannel', () => ({ dispatchBroadcastChannel: vi.fn() }));

describe('logout during token refresh', () => {
    afterEach(() => {
        cleanup();
        vi.unstubAllGlobals();
        for (const name of ['accessToken-1', 'refreshToken-1', 'refreshTokenPresent-1']) {
            deleteCookie(name, { path: '/' });
        }
    });

    test.each([
        false,
        true,
    ])('late refresh cookies cannot restore the session after logout from another client (valid access cookie: %s)', async (hasValidAccessCookie) => {
        const queues = new Map<string, Promise<unknown>>();
        const locks = {
            request: vi.fn((name: string, _options: LockOptions, callback: LockGrantedCallback<unknown>) => {
                const result = (queues.get(name) ?? Promise.resolve()).then(() =>
                    callback({ name, mode: 'exclusive' }),
                );
                queues.set(
                    name,
                    result.catch(() => undefined),
                );

                return result;
            }),
        };
        vi.stubGlobal('navigator', { locks });
        setCookie('refreshToken-1', 'original-refresh', { path: '/' });
        setCookie('refreshTokenPresent-1', '1', { path: '/' });
        let releaseRefresh: () => void = () => {};
        const refreshResponse = new Promise<void>((resolve) => {
            releaseRefresh = resolve;
        });
        let refreshStarted = false;
        let logoutStarted = false;
        vi.stubGlobal(
            'fetch',
            vi.fn(async (_input, init: RequestInit) => {
                const { operationName } = JSON.parse(init.body as string);
                if (operationName === 'RefreshTokens') {
                    refreshStarted = true;
                    await refreshResponse;
                    // Browser cookies are applied before fetch resolves with the refresh response.
                    setCookie('accessToken-1', 'rotated-access', { path: '/' });
                    setCookie('refreshToken-1', 'rotated-refresh', { path: '/' });
                    setCookie('refreshTokenPresent-1', '1', { path: '/' });

                    return Response.json({
                        data: { RefreshTokens: { accessToken: 'rotated-access', refreshToken: '' } },
                    });
                }
                if (operationName === 'LogoutMutation') {
                    logoutStarted = true;
                    for (const name of ['accessToken-1', 'refreshToken-1', 'refreshTokenPresent-1']) {
                        deleteCookie(name, { path: '/' });
                    }

                    return Response.json({ data: { Logout: true } });
                }

                return Response.json({
                    data: {
                        currentCustomerUser: new Headers(init.headers).has('X-Auth-Token')
                            ? { uuid: 'customer' }
                            : null,
                    },
                });
            }),
        );
        const createClient = () =>
            new Client({
                url: 'https://test.ts/graphql/',
                preferGetMethod: false,
                exchanges: [authExchange(getAuthExchangeOptions({ domainId: 1 } as never)), fetchExchange],
            });
        const refreshingClient = createClient();
        const logoutClient = createClient();
        const query = parse('query CurrentCustomer { currentCustomerUser { uuid } }');
        const pendingQuery = refreshingClient.query(query, {}).toPromise();
        await waitFor(() => expect(refreshStarted).toBe(true));
        // A valid cookie lets the second client's logout reach the network without its own prerequisite refresh.
        if (hasValidAccessCookie) {
            setCookie('accessToken-1', 'still-valid-access', { path: '/' });
        }
        const wrapper = ({ children }: PropsWithChildren) => <Provider value={logoutClient}>{children}</Provider>;
        const { result } = renderHook(() => useLogout(), { wrapper });

        await act(async () => {
            const logout = result.current();
            try {
                await waitFor(() => expect(logoutStarted || locks.request.mock.calls.length === 2).toBe(true));
                if (logoutStarted) {
                    await logout;
                }
            } finally {
                releaseRefresh();
            }
            await Promise.all([pendingQuery, logout]);
        });

        expect(getCookie('accessToken-1')).toBeUndefined();
        expect(getCookie('refreshToken-1')).toBeUndefined();
        expect(getCookie('refreshTokenPresent-1')).toBeUndefined();
        const afterLogout = await logoutClient.query(query, {}).toPromise();
        expect(afterLogout.error).toBeUndefined();
        expect(afterLogout.data?.currentCustomerUser).toBeNull();
    });
});
