import { act, renderHook } from '@testing-library/react';
import { TypeLoginTypeEnum } from 'graphql/types';
import { useSessionStore } from 'store/useSessionStore';
import { useHandleActionsAfterLogin, useLogin, useLoginAfterPasswordRecovery } from 'utils/auth/useLogin';
import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest';

const {
    dispatchBroadcastChannelMock,
    loginMutationMock,
    performAuthHardNavigationMock,
    persistStoreState,
    storeAuthNotificationMock,
} = vi.hoisted(() => ({
    dispatchBroadcastChannelMock: vi.fn(),
    loginMutationMock: vi.fn(),
    performAuthHardNavigationMock: vi.fn(),
    persistStoreState: {
        productListUuids: {},
        updateCartUuid: vi.fn(),
        updateLastLoginType: vi.fn(),
        updateProductListUuids: vi.fn(),
        updateUserEntryState: vi.fn(),
    },
    storeAuthNotificationMock: vi.fn(),
}));

vi.mock('components/providers/DomainConfigProvider', () => ({
    useDomainConfig: () => ({ domainId: 1 }),
}));

vi.mock('graphql/requests/auth/mutations/LoginMutation.generated', () => ({
    useLoginMutation: () => [{}, loginMutationMock],
}));

vi.mock('store/usePersistStore', () => ({
    usePersistStore: (selector: (state: typeof persistStoreState) => unknown) => selector(persistStoreState),
}));

vi.mock('utils/auth/performAuthHardNavigation', () => ({
    performAuthHardNavigation: performAuthHardNavigationMock,
}));

vi.mock('utils/auth/authMutationFetcher', () => ({
    getAuthMutationFetcher: vi.fn(),
}));

vi.mock('utils/auth/authNotificationStorage', () => ({
    storeAuthNotification: storeAuthNotificationMock,
}));

vi.mock('utils/useBroadcastChannel', () => ({
    dispatchBroadcastChannel: dispatchBroadcastChannelMock,
}));

const initialSessionState = useSessionStore.getState();

afterEach(() => {
    act(() => useSessionStore.setState(initialSessionState, true));
});

test.each([
    ['login', useHandleActionsAfterLogin],
    ['password recovery', useLoginAfterPasswordRecovery],
] as const)('keeps the cart unavailable before clearing its guest identity after %s', (_flow, useLoginHandler) => {
    const { result } = renderHook(() => useLoginHandler());
    let wasCartStaleWhenIdentityWasCleared: boolean | undefined;
    persistStoreState.updateCartUuid.mockImplementationOnce(() => {
        wasCartStaleWhenIdentityWasCleared = useSessionStore.getState().isCartStale;
    });

    act(() => {
        result.current(false, undefined);
    });

    expect(wasCartStaleWhenIdentityWasCleared).toBe(true);
    expect(useSessionStore.getState().isCartStale).toBe(true);
});

describe('useHandleActionsAfterLogin', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    test('stores feedback before performing one hard navigation', () => {
        const { result } = renderHook(() => useHandleActionsAfterLogin());

        act(() => {
            result.current(true, '/customer');
        });

        expect(storeAuthNotificationMock).toHaveBeenCalledWith(1, 'login-with-cart-modifications');
        expect(performAuthHardNavigationMock).toHaveBeenCalledOnce();
        expect(performAuthHardNavigationMock).toHaveBeenCalledWith('/customer');
        expect(storeAuthNotificationMock.mock.invocationCallOrder[0]).toBeLessThan(
            performAuthHardNavigationMock.mock.invocationCallOrder[0],
        );
    });

    test('stores the successfully used login method', () => {
        const { result } = renderHook(() => useHandleActionsAfterLogin());

        act(() => {
            result.current(false, '/', TypeLoginTypeEnum.Google);
        });

        expect(persistStoreState.updateLastLoginType).toHaveBeenCalledWith(TypeLoginTypeEnum.Google);
    });

    test('keeps the last login method when the successful flow does not represent a customer-selected method', () => {
        const { result } = renderHook(() => useHandleActionsAfterLogin());

        act(() => {
            result.current(false, '/');
        });

        expect(persistStoreState.updateLastLoginType).not.toHaveBeenCalled();
    });
});

describe('useLogin', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    test('closes the login popup before navigation and stores the successful login method', async () => {
        act(() => useSessionStore.getState().updatePortalContent(<div>Login form</div>));
        loginMutationMock.mockResolvedValue({ data: { Login: { showCartMergeInfo: false } } });
        performAuthHardNavigationMock.mockImplementationOnce(() => {
            expect(useSessionStore.getState().portalContent).toBeNull();
        });
        const { result } = renderHook(() => useLogin());

        await act(async () => {
            await result.current({ email: 'customer@example.com', password: 'password' });
        });

        expect(persistStoreState.updateLastLoginType).toHaveBeenCalledWith(TypeLoginTypeEnum.Web);
        expect(performAuthHardNavigationMock).toHaveBeenCalledOnce();
    });

    test('keeps the popup open and the last login method after an unsuccessful mutation', async () => {
        const loginPopup = <div>Login form</div>;
        act(() => useSessionStore.getState().updatePortalContent(loginPopup));
        loginMutationMock.mockResolvedValue({ error: new Error('Invalid credentials') });
        const { result } = renderHook(() => useLogin());

        await act(async () => {
            await result.current({ email: 'customer@example.com', password: 'invalid-password' });
        });

        expect(persistStoreState.updateLastLoginType).not.toHaveBeenCalled();
        expect(useSessionStore.getState().isCartStale).toBe(false);
        expect(useSessionStore.getState().portalContent).toBe(loginPopup);
        expect(performAuthHardNavigationMock).not.toHaveBeenCalled();
    });
});
