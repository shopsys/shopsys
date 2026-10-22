import { act, renderHook } from '@testing-library/react';
import { toast } from 'react-toastify';
import { useLogoutStore } from 'store/useLogoutStore';
import { CombinedError } from 'urql';
import { useLogout } from 'utils/auth/useLogout';
import { beforeEach, describe, expect, test, vi } from 'vitest';

const {
    dispatchBroadcastChannelMock,
    logoutMutationMock,
    performAuthHardNavigationMock,
    persistStoreState,
    showErrorMessageMock,
    storeAuthNotificationMock,
} = vi.hoisted(() => ({
    dispatchBroadcastChannelMock: vi.fn(),
    logoutMutationMock: vi.fn(),
    performAuthHardNavigationMock: vi.fn(),
    persistStoreState: {
        resetContactInformation: vi.fn(),
        updateProductListUuids: vi.fn(),
    },
    showErrorMessageMock: vi.fn(),
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

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({ t: (message: string) => message }),
}));

vi.mock('react-toastify', () => ({ toast: { loading: vi.fn(), update: vi.fn(), dismiss: vi.fn() } }));

vi.mock('utils/toasts/showErrorMessage', () => ({
    showErrorMessage: showErrorMessageMock,
}));

describe('useLogout', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        useLogoutStore.setState({ logoutDeadlineByDomain: {} });
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
        expect(showErrorMessageMock).not.toHaveBeenCalled();
        expect(storeAuthNotificationMock.mock.invocationCallOrder[0]).toBeLessThan(
            performAuthHardNavigationMock.mock.invocationCallOrder[0],
        );
    });

    test.each([
        ['network failure', { error: new CombinedError({ networkError: new TypeError('Failed to fetch') }) }],
        ['rejected logout', { data: { Logout: false } }],
    ])('shows actionable feedback after %s without reporting a successful logout', async (_scenario, response) => {
        logoutMutationMock.mockResolvedValue(response);
        const { result } = renderHook(() => useLogout());

        await act(async () => {
            await result.current();
        });

        expect(showErrorMessageMock).toHaveBeenCalledExactlyOnceWith(
            'Unable to log out. Please try again.',
            undefined,
            expect.objectContaining({ action: expect.anything() }),
        );
        expect(toast.dismiss).toHaveBeenCalledWith(expect.stringMatching(/^logout-1-/));
        expect(useLogoutStore.getState().logoutDeadlineByDomain[1]).toBeUndefined();
        expect(persistStoreState.resetContactInformation).not.toHaveBeenCalled();
        expect(persistStoreState.updateProductListUuids).not.toHaveBeenCalled();
        expect(storeAuthNotificationMock).not.toHaveBeenCalled();
        expect(dispatchBroadcastChannelMock).not.toHaveBeenCalled();
        expect(performAuthHardNavigationMock).not.toHaveBeenCalled();
    });

    test('a thrown mutation error releases the busy state and displays retry feedback', async () => {
        logoutMutationMock.mockRejectedValueOnce(new TypeError('Failed to fetch'));
        const { result } = renderHook(() => useLogout());

        await act(async () => {
            await result.current();
        });

        expect(showErrorMessageMock).toHaveBeenCalledOnce();
        expect(useLogoutStore.getState().logoutDeadlineByDomain[1]).toBeUndefined();
        expect(performAuthHardNavigationMock).not.toHaveBeenCalled();
    });
});
