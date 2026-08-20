import { renderHook } from '@testing-library/react';
import { consumeAuthNotification, storeAuthNotification } from 'utils/auth/authNotificationStorage';
import { useAuthStateSynchronization } from 'utils/auth/useAuthStateSynchronization';
import { beforeEach, describe, expect, test, vi } from 'vitest';

const { authState, reloadMock } = vi.hoisted(() => ({
    authState: {
        accessToken: 'access-token' as string | undefined,
        domainId: 1,
        isCustomerUserFetching: false,
        isCustomerUserStale: false,
        isUserLoggedIn: true,
        hasRefreshToken: true,
        path: '/customer',
    },
    reloadMock: vi.fn(),
}));

vi.mock('components/providers/DomainConfigProvider', () => ({
    useDomainConfig: () => ({ domainId: authState.domainId }),
}));

vi.mock('graphql/requests/customer/queries/CurrentCustomerUserQuery.generated', () => ({
    useCurrentCustomerUserQuery: () => [
        { fetching: authState.isCustomerUserFetching, stale: authState.isCustomerUserStale },
    ],
}));

vi.mock('next/router', () => ({
    useRouter: () => ({ asPath: authState.path, reload: reloadMock }),
}));

vi.mock('utils/auth/getTokensFromCookies', () => ({
    getAccessTokenFromCookies: () => authState.accessToken,
    hasRefreshTokenInCookies: () => authState.hasRefreshToken,
}));

vi.mock('utils/auth/useIsUserLoggedIn', () => ({
    useIsUserLoggedIn: () => authState.isUserLoggedIn,
}));

describe('useAuthStateSynchronization', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        sessionStorage.clear();
        authState.accessToken = 'access-token';
        authState.domainId = 1;
        authState.isCustomerUserFetching = false;
        authState.isCustomerUserStale = false;
        authState.isUserLoggedIn = true;
        authState.hasRefreshToken = true;
        authState.path = '/customer';
    });

    test('keeps an authenticated page when authentication cookies are present', () => {
        renderHook(() => useAuthStateSynchronization());

        expect(reloadMock).not.toHaveBeenCalled();
    });

    test('keeps an anonymous page when authentication cookies are absent', () => {
        authState.accessToken = undefined;
        authState.isUserLoggedIn = false;
        authState.hasRefreshToken = false;

        renderHook(() => useAuthStateSynchronization());

        expect(reloadMock).not.toHaveBeenCalled();
    });

    test('reloads an authenticated page when authentication cookies are absent', () => {
        authState.accessToken = undefined;
        authState.hasRefreshToken = false;

        renderHook(() => useAuthStateSynchronization());

        expect(reloadMock).toHaveBeenCalledOnce();
    });

    test('reloads an anonymous page when authentication cookies are present', () => {
        authState.isUserLoggedIn = false;

        renderHook(() => useAuthStateSynchronization());

        expect(reloadMock).toHaveBeenCalledOnce();
    });

    test('waits for the customer user query before checking authentication state', () => {
        authState.accessToken = undefined;
        authState.hasRefreshToken = false;
        authState.isCustomerUserFetching = true;

        renderHook(() => useAuthStateSynchronization());

        expect(reloadMock).not.toHaveBeenCalled();
    });

    test('waits for stale customer user data to be revalidated before checking authentication state', () => {
        authState.accessToken = undefined;
        authState.hasRefreshToken = false;
        authState.isCustomerUserStale = true;

        renderHook(() => useAuthStateSynchronization());

        expect(reloadMock).not.toHaveBeenCalled();
    });

    test('does not reload an expected authentication mismatch while an authentication transition is pending', () => {
        authState.isUserLoggedIn = false;
        storeAuthNotification(1, 'registration');

        renderHook(() => useAuthStateSynchronization());

        expect(reloadMock).not.toHaveBeenCalled();
    });

    test('reloads a mismatched authentication state after the authentication transition finishes', () => {
        authState.isUserLoggedIn = false;
        storeAuthNotification(1, 'registration');
        const { rerender } = renderHook(() => useAuthStateSynchronization());

        consumeAuthNotification(1);
        authState.path = '/customer/orders';
        rerender();

        expect(reloadMock).toHaveBeenCalledOnce();
    });

    test('checks authentication state again after client-side navigation', () => {
        const { rerender } = renderHook(() => useAuthStateSynchronization());
        authState.accessToken = undefined;
        authState.hasRefreshToken = false;
        authState.path = '/customer/orders';

        rerender();

        expect(reloadMock).toHaveBeenCalledOnce();
    });
});
