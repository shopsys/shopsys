import { act, cleanup, renderHook, waitFor } from '@testing-library/react';
import { authExchange } from '@urql/exchange-auth';
import { deleteCookie, getCookie, setCookie } from 'cookies-next';
import { parse } from 'graphql';
import { PropsWithChildren } from 'react';
import { toast } from 'react-toastify';
import { useLogoutStore } from 'store/useLogoutStore';
import { Client, fetchExchange, Provider } from 'urql';
import { getAuthExchangeOptions } from 'urql/authExchange';
import { storeAuthNotification } from 'utils/auth/authNotificationStorage';
import { performAuthHardNavigation } from 'utils/auth/performAuthHardNavigation';
import { useLogout } from 'utils/auth/useLogout';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';
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
vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({ t: (message: string) => message }),
}));
vi.mock('utils/toasts/showErrorMessage', () => ({ showErrorMessage: vi.fn() }));
vi.mock('react-toastify', () => ({ toast: { loading: vi.fn(), update: vi.fn(), dismiss: vi.fn() } }));

describe('logout during token refresh', () => {
    afterEach(() => {
        cleanup();
        useLogoutStore.setState({ logoutDeadlineByDomain: {} });
        vi.unstubAllGlobals();
        vi.useRealTimers();
        for (const name of ['accessToken-1', 'refreshToken-1', 'refreshTokenPresent-1']) {
            deleteCookie(name, { path: '/' });
        }
    });

    test.each([
        { hasAccess: true, existingRefresh: false },
        { hasAccess: false, existingRefresh: false },
        { hasAccess: false, existingRefresh: true },
    ])('completes one logout after a thirty-second lock wait ($hasAccess access, $existingRefresh existing refresh)', async ({
        hasAccess,
        existingRefresh,
    }) => {
        vi.useFakeTimers();
        let lockHeld = true;
        const waiting: (() => void)[] = [];
        const request = vi.fn(
            (name: string, options: LockOptions, callback: LockGrantedCallback<unknown>) =>
                new Promise((resolve, reject) => {
                    const acquire = () => {
                        if (!options.signal?.aborted) resolve(callback({ name, mode: 'exclusive' }));
                    };
                    options.signal?.addEventListener('abort', () => reject(new DOMException('Aborted', 'AbortError')));
                    if (lockHeld) waiting.push(acquire);
                    else acquire();
                }),
        );
        vi.stubGlobal('navigator', { locks: { request } });
        setCookie('refreshToken-1', 'original', { path: '/' });
        setCookie('refreshTokenPresent-1', '1', { path: '/' });
        if (hasAccess) setCookie('accessToken-1', 'original', { path: '/' });
        const fetchMock = vi.fn(async (_input, init: RequestInit) => {
            const { operationName } = JSON.parse(init.body as string);
            if (operationName === 'RefreshTokens') {
                setCookie('accessToken-1', 'rotated-access', { path: '/' });
                setCookie('refreshToken-1', 'rotated-refresh', { path: '/' });
                return Response.json({ data: { RefreshTokens: { accessToken: 'rotated-access', refreshToken: '' } } });
            }
            if (operationName === 'CurrentCustomer')
                return Response.json({ data: { currentCustomerUser: { uuid: 'customer' } } });
            expect(operationName).toBe('LogoutMutation');
            for (const name of ['accessToken-1', 'refreshToken-1', 'refreshTokenPresent-1'])
                deleteCookie(name, { path: '/' });
            return Response.json({ data: { Logout: true } });
        });
        vi.stubGlobal('fetch', fetchMock);
        const client = new Client({
            url: 'https://test.ts/graphql/',
            preferGetMethod: false,
            exchanges: [authExchange(getAuthExchangeOptions({ domainId: 1 } as never)), fetchExchange],
        });
        const pendingQuery = existingRefresh
            ? client.query(parse('query CurrentCustomer { currentCustomerUser { uuid } }'), {}).toPromise()
            : undefined;
        if (existingRefresh) await vi.advanceTimersByTimeAsync(5_000);
        const wrapper = ({ children }: PropsWithChildren) => <Provider value={client}>{children}</Provider>;
        const first = renderHook(() => useLogout(), { wrapper });
        const second = renderHook(() => useLogout(), { wrapper });

        await act(async () => {
            const logout = first.result.current();
            first.unmount();
            const secondLogout = second.result.current();
            await vi.advanceTimersByTimeAsync(10_000);
            expect(toast.loading).toHaveBeenCalledOnce();
            expect(toast.update).toHaveBeenCalledWith(
                expect.stringMatching(/^logout-1-/),
                expect.objectContaining({ render: 'Logging out is taking longer than usual. Please wait.' }),
            );
            expect(fetchMock).not.toHaveBeenCalled();
            expect(showErrorMessage).not.toHaveBeenCalled();
            await vi.advanceTimersByTimeAsync(20_000);
            lockHeld = false;
            waiting.splice(0).forEach((acquire) => acquire());
            await Promise.all([logout, secondLogout, pendingQuery]);
        });

        const operations = fetchMock.mock.calls.map(([, init]) => JSON.parse(init.body as string).operationName);
        expect(operations.filter((name) => name === 'LogoutMutation')).toHaveLength(1);
        expect(operations.filter((name) => name === 'RefreshTokens')).toHaveLength(hasAccess ? 0 : 1);
        expect(storeAuthNotification).toHaveBeenCalledExactlyOnceWith(1, 'logout');
        expect(performAuthHardNavigation).toHaveBeenCalledOnce();
        expect(showErrorMessage).not.toHaveBeenCalled();
        expect(toast.dismiss).toHaveBeenCalledWith(expect.stringMatching(/^logout-1-/));
        for (const name of ['accessToken-1', 'refreshToken-1', 'refreshTokenPresent-1'])
            expect(getCookie(name)).toBeUndefined();
    });

    test('a permanently held lock fails after sixty seconds without clearing cookies or sending logout', async () => {
        vi.useFakeTimers();
        vi.stubGlobal('navigator', {
            locks: {
                request: vi.fn(
                    (_name, options: LockOptions) =>
                        new Promise((_resolve, reject) => {
                            options.signal?.addEventListener('abort', () =>
                                reject(new DOMException('Aborted', 'AbortError')),
                            );
                        }),
                ),
            },
        });
        setCookie('accessToken-1', 'original', { path: '/' });
        setCookie('refreshTokenPresent-1', '1', { path: '/' });
        setCookie('refreshToken-1', 'original', { path: '/' });
        const fetchMock = vi.fn();
        vi.stubGlobal('fetch', fetchMock);
        const client = new Client({
            url: 'https://test.ts/graphql/',
            exchanges: [authExchange(getAuthExchangeOptions({ domainId: 1 } as never)), fetchExchange],
        });
        const wrapper = ({ children }: PropsWithChildren) => <Provider value={client}>{children}</Provider>;
        const { result } = renderHook(() => useLogout(), { wrapper });

        await act(async () => {
            const logout = result.current();
            await vi.advanceTimersByTimeAsync(59_999);
            expect(showErrorMessage).not.toHaveBeenCalled();
            await vi.advanceTimersByTimeAsync(1);
            await logout;
        });

        expect(showErrorMessage).toHaveBeenCalledOnce();
        expect(fetchMock).not.toHaveBeenCalled();
        expect(performAuthHardNavigation).not.toHaveBeenCalled();
        expect(getCookie('accessToken-1')).toBe('original');
        expect(getCookie('refreshToken-1')).toBe('original');
        expect(getCookie('refreshTokenPresent-1')).toBe('1');
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
