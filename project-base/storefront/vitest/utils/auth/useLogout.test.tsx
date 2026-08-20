import { act, renderHook } from '@testing-library/react';
import { useLogout } from 'utils/auth/useLogout';
import { beforeEach, describe, expect, test, vi } from 'vitest';

const {
    dispatchBroadcastChannelMock,
    logoutMutationMock,
    performAuthHardNavigationMock,
    persistStoreState,
    storeAuthNotificationMock,
} = vi.hoisted(() => ({
    dispatchBroadcastChannelMock: vi.fn(),
    logoutMutationMock: vi.fn(),
    performAuthHardNavigationMock: vi.fn(),
    persistStoreState: {
        resetContactInformation: vi.fn(),
        updateProductListUuids: vi.fn(),
    },
    storeAuthNotificationMock: vi.fn(),
}));

vi.mock('components/providers/DomainConfigProvider', () => ({
    useDomainConfig: () => ({ domainId: 1 }),
}));

vi.mock('graphql/requests/auth/mutations/LogoutMutation.generated', () => ({
    useLogoutMutation: () => [{}, logoutMutationMock],
}));

vi.mock('store/usePersistStore', () => ({
    usePersistStore: (selector: (state: typeof persistStoreState) => unknown) => selector(persistStoreState),
}));

vi.mock('utils/auth/authMutationFetcher', () => ({
    getAuthMutationFetcher: vi.fn(),
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

describe('useLogout', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        logoutMutationMock.mockResolvedValue({ data: { Logout: true } });
    });

    test('stores feedback before performing one hard reload', async () => {
        const { result } = renderHook(() => useLogout());

        await act(async () => {
            await result.current();
        });

        expect(storeAuthNotificationMock).toHaveBeenCalledWith(1, 'logout');
        expect(performAuthHardNavigationMock).toHaveBeenCalledOnce();
        expect(performAuthHardNavigationMock).toHaveBeenCalledWith();
        expect(storeAuthNotificationMock.mock.invocationCallOrder[0]).toBeLessThan(
            performAuthHardNavigationMock.mock.invocationCallOrder[0],
        );
    });
});
