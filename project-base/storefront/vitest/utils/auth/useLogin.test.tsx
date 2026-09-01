import { act, renderHook } from '@testing-library/react';
import { useHandleActionsAfterLogin } from 'utils/auth/useLogin';
import { beforeEach, describe, expect, test, vi } from 'vitest';

const { dispatchBroadcastChannelMock, performAuthHardNavigationMock, persistStoreState, storeAuthNotificationMock } =
    vi.hoisted(() => ({
        dispatchBroadcastChannelMock: vi.fn(),
        performAuthHardNavigationMock: vi.fn(),
        persistStoreState: {
            updateCartUuid: vi.fn(),
            updateProductListUuids: vi.fn(),
            updateUserEntryState: vi.fn(),
        },
        storeAuthNotificationMock: vi.fn(),
    }));

vi.mock('components/providers/DomainConfigProvider', () => ({
    useDomainConfig: () => ({ domainId: 1 }),
}));

vi.mock('store/usePersistStore', () => ({
    usePersistStore: (selector: (state: typeof persistStoreState) => unknown) => selector(persistStoreState),
}));

vi.mock('utils/auth/performAuthHardNavigation', () => ({
    performAuthHardNavigation: performAuthHardNavigationMock,
}));

vi.mock('utils/auth/authNotificationStorage', () => ({
    storeAuthNotification: storeAuthNotificationMock,
}));

vi.mock('utils/useBroadcastChannel', () => ({
    dispatchBroadcastChannel: dispatchBroadcastChannelMock,
}));

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
});
