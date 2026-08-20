import { act, render, screen } from '@testing-library/react';
import { DeferredToastContainer } from 'components/Pages/App/DeferredToastContainer';
import { ToastContainerWithAuthNotifications } from 'components/Pages/App/ToastContainerWithAuthNotifications';
import { TypeLoginTypeEnum } from 'graphql/types';
import { StrictMode } from 'react';
import { toast } from 'react-toastify';
import {
    consumeAuthNotification,
    getAuthNotification,
    storeAuthNotification,
} from 'utils/auth/authNotificationStorage';
import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest';

const { authState, reloadMock, routerEvents } = vi.hoisted(() => ({
    authState: {
        hasAccessToken: true,
        hasRefreshToken: true,
        isCustomerUserFetching: false,
        isCustomerUserStale: false,
        isUserLoggedIn: true,
    },
    reloadMock: vi.fn(),
    routerEvents: {
        routeChangeCompleteHandler: undefined as (() => void) | undefined,
        off: vi.fn(),
        on: vi.fn(),
    },
}));

vi.mock('components/providers/DomainConfigProvider', () => ({
    useDomainConfig: () => ({ domainId: 1 }),
}));

vi.mock('graphql/requests/customer/queries/CurrentCustomerUserQuery.generated', () => ({
    useCurrentCustomerUserQuery: () => [
        {
            data: { currentCustomerUser: authState.isUserLoggedIn ? {} : null },
            fetching: authState.isCustomerUserFetching,
            stale: authState.isCustomerUserStale,
        },
    ],
}));

vi.mock('next/router', () => ({
    useRouter: () => ({ events: routerEvents, reload: reloadMock }),
}));

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({
        t: (key: string, options?: Record<string, string>) =>
            key.replace('{{ socialNetworkType }}', options?.socialNetworkType ?? ''),
    }),
}));

vi.mock('utils/auth/getTokensFromCookies', () => ({
    getAccessTokenFromCookies: () => (authState.hasAccessToken ? 'access-token' : undefined),
    hasRefreshTokenInCookies: () => authState.hasRefreshToken,
}));

vi.mock('utils/useDeferredRender', () => ({
    useDeferredRender: () => false,
}));

const renderToastNotifications = () =>
    render(
        <StrictMode>
            <ToastContainerWithAuthNotifications />
        </StrictMode>,
    );

describe('AuthNotificationLoader', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        sessionStorage.clear();
        authState.hasAccessToken = true;
        authState.hasRefreshToken = true;
        authState.isCustomerUserFetching = false;
        authState.isCustomerUserStale = false;
        authState.isUserLoggedIn = true;
        routerEvents.routeChangeCompleteHandler = undefined;
        routerEvents.on.mockImplementation((event: string, handler: () => void) => {
            if (event === 'routeChangeComplete') {
                routerEvents.routeChangeCompleteHandler = handler;
            }
        });
    });

    afterEach(() => {
        act(() => {
            toast.dismiss();
            toast.clearWaitingQueue();
        });
    });

    test('shows and consumes a notification after a document navigation in strict mode', async () => {
        storeAuthNotification(1, 'login');

        renderToastNotifications();

        expect(await screen.findByText('Successfully logged in')).toBeInTheDocument();
        expect(consumeAuthNotification(1)).toBeNull();
    });

    test('does not defer a stored authentication notification', async () => {
        storeAuthNotification(1, 'login');

        const { rerender } = render(
            <StrictMode>
                <DeferredToastContainer />
            </StrictMode>,
        );

        expect(await screen.findByText('Successfully logged in')).toBeInTheDocument();
        expect(consumeAuthNotification(1)).toBeNull();

        rerender(
            <StrictMode>
                <DeferredToastContainer />
            </StrictMode>,
        );
        expect(screen.getByText('Successfully logged in')).toBeInTheDocument();
    });

    test('shows a stored notification after client-side navigation', async () => {
        renderToastNotifications();
        storeAuthNotification(1, 'registration');

        act(() => {
            routerEvents.routeChangeCompleteHandler?.();
        });

        expect(await screen.findByText('Your account has been created and you are logged in now')).toBeInTheDocument();
        expect(consumeAuthNotification(1)).toBeNull();
    });

    test('does not consume a notification created in the current document before navigation finishes', async () => {
        authState.hasAccessToken = false;
        authState.hasRefreshToken = false;
        authState.isUserLoggedIn = false;
        const { rerender } = renderToastNotifications();

        storeAuthNotification(1, 'registration');
        authState.hasAccessToken = true;
        authState.hasRefreshToken = true;
        authState.isUserLoggedIn = true;
        rerender(
            <StrictMode>
                <ToastContainerWithAuthNotifications />
            </StrictMode>,
        );

        expect(screen.queryByText('Your account has been created and you are logged in now')).not.toBeInTheDocument();
        expect(getAuthNotification(1)).toBe('registration');

        act(() => {
            routerEvents.routeChangeCompleteHandler?.();
        });

        expect(await screen.findByText('Your account has been created and you are logged in now')).toBeInTheDocument();
        expect(getAuthNotification(1)).toBeNull();
    });

    test('keeps a registration notification across a reload caused by authentication state mismatch', async () => {
        authState.isUserLoggedIn = false;
        const { unmount } = renderToastNotifications();
        storeAuthNotification(1, 'registration');

        act(() => {
            routerEvents.routeChangeCompleteHandler?.();
        });

        expect(reloadMock).toHaveBeenCalledOnce();
        expect(screen.queryByText('Your account has been created and you are logged in now')).not.toBeInTheDocument();
        expect(getAuthNotification(1)).toBe('registration');

        unmount();
        authState.isUserLoggedIn = true;
        renderToastNotifications();

        expect(await screen.findByText('Your account has been created and you are logged in now')).toBeInTheDocument();
        expect(getAuthNotification(1)).toBeNull();
    });

    test('keeps a login notification stored while the customer query is fetching', async () => {
        authState.isCustomerUserFetching = true;
        storeAuthNotification(1, 'login');
        const { rerender } = renderToastNotifications();

        expect(screen.queryByText('Successfully logged in')).not.toBeInTheDocument();
        expect(getAuthNotification(1)).toBe('login');

        authState.isCustomerUserFetching = false;
        rerender(
            <StrictMode>
                <ToastContainerWithAuthNotifications />
            </StrictMode>,
        );

        expect(await screen.findByText('Successfully logged in')).toBeInTheDocument();
        expect(getAuthNotification(1)).toBeNull();
    });

    test('reloads after the customer query settles with an authentication state mismatch', () => {
        authState.isCustomerUserFetching = true;
        authState.isUserLoggedIn = false;
        storeAuthNotification(1, 'login');
        const { rerender } = renderToastNotifications();

        expect(reloadMock).not.toHaveBeenCalled();

        authState.isCustomerUserFetching = false;
        rerender(
            <StrictMode>
                <ToastContainerWithAuthNotifications />
            </StrictMode>,
        );

        expect(reloadMock).toHaveBeenCalledOnce();
        expect(getAuthNotification(1)).toBe('login');
    });

    test('keeps a logout notification stored until authentication state synchronization finishes', async () => {
        authState.hasAccessToken = false;
        authState.hasRefreshToken = false;
        authState.isCustomerUserStale = true;
        storeAuthNotification(1, 'logout');
        const { rerender } = renderToastNotifications();

        expect(screen.queryByText('Successfully logged out')).not.toBeInTheDocument();
        expect(getAuthNotification(1)).toBe('logout');

        authState.isCustomerUserStale = false;
        authState.isUserLoggedIn = false;
        rerender(
            <StrictMode>
                <ToastContainerWithAuthNotifications />
            </StrictMode>,
        );

        expect(await screen.findByText('Successfully logged out')).toBeInTheDocument();
        expect(getAuthNotification(1)).toBeNull();
    });

    test.each([
        ['login-with-cart-modifications', 'Successfully logged in'],
        ['registration-with-cart-modifications', 'Your account has been created and you are logged in now'],
    ] as const)('shows both messages for %s exactly once', async (authNotification, successMessage) => {
        storeAuthNotification(1, authNotification);

        renderToastNotifications();

        expect(await screen.findByText(successMessage)).toBeInTheDocument();
        expect(screen.getByText('Your cart has been modified. Please check the changes.')).toBeInTheDocument();

        act(() => {
            routerEvents.routeChangeCompleteHandler?.();
        });

        expect(screen.getAllByText(successMessage)).toHaveLength(1);
        expect(screen.getAllByText('Your cart has been modified. Please check the changes.')).toHaveLength(1);
        expect(getAuthNotification(1)).toBeNull();
    });

    test('renders a social network name immediately even when authentication state is mismatched', async () => {
        authState.isUserLoggedIn = false;
        storeAuthNotification(1, {
            type: 'social-login-fail',
            socialNetworkType: TypeLoginTypeEnum.Google,
        });

        renderToastNotifications();

        expect(await screen.findByText('Login via google is not possible. Please register.')).toBeInTheDocument();
    });
});
