import { act, renderHook } from '@testing-library/react';
import { TypeRegistrationByOrderInput, TypeRegistrationDataInput } from 'graphql/types';
import { useRegistration } from 'utils/auth/useRegistration';
import { beforeEach, describe, expect, test, vi } from 'vitest';

const {
    performAuthHardNavigationMock,
    persistStoreState,
    registerByOrderMutationMock,
    registerMutationMock,
    routerReplaceMock,
    sessionStoreState,
    storeAuthNotificationMock,
} = vi.hoisted(() => ({
    performAuthHardNavigationMock: vi.fn(),
    persistStoreState: {
        productListUuids: {},
        updateCartUuid: vi.fn(),
        updateProductListUuids: vi.fn(),
        updateUserEntryState: vi.fn(),
    },
    registerByOrderMutationMock: vi.fn(),
    registerMutationMock: vi.fn(),
    routerReplaceMock: vi.fn(),
    sessionStoreState: {
        updatePageLoadingState: vi.fn(),
    },
    storeAuthNotificationMock: vi.fn(),
}));

vi.mock('components/providers/DomainConfigProvider', () => ({
    useDomainConfig: () => ({ domainId: 1 }),
}));

vi.mock('graphql/requests/registration/mutations/RegistrationByOrderMutation.generated', () => ({
    useRegistrationByOrderMutation: () => [{}, registerByOrderMutationMock],
}));

vi.mock('graphql/requests/registration/mutations/RegistrationMutation.generated', () => ({
    useRegistrationMutation: () => [{}, registerMutationMock],
}));

vi.mock('gtm/handlers/onGtmSendFormEventHandler', () => ({
    onGtmSendFormEventHandler: vi.fn(),
}));

vi.mock('next/router', () => ({
    useRouter: () => ({ replace: routerReplaceMock }),
}));

vi.mock('store/usePersistStore', () => ({
    usePersistStore: (selector: (state: typeof persistStoreState) => unknown) => selector(persistStoreState),
}));

vi.mock('store/useSessionStore', () => ({
    useSessionStore: (selector: (state: typeof sessionStoreState) => unknown) => selector(sessionStoreState),
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

vi.mock('utils/forms/blurInput', () => ({
    blurInput: vi.fn(),
}));

describe('useRegistration', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        routerReplaceMock.mockResolvedValue(true);
    });

    test('navigates to homepage without reload and exposes feedback after registration', async () => {
        registerMutationMock.mockResolvedValue({
            data: { Register: { showCartMergeInfo: false } },
        });
        const { result } = renderHook(() => useRegistration());

        await act(async () => {
            await result.current.register({} as Omit<TypeRegistrationDataInput, 'productListsUuids'>);
        });

        expect(routerReplaceMock).toHaveBeenCalledOnce();
        expect(routerReplaceMock).toHaveBeenCalledWith('/');
        expect(sessionStoreState.updatePageLoadingState).toHaveBeenCalledWith({
            isPageLoading: true,
            redirectPageType: 'homepage',
        });
        expect(storeAuthNotificationMock).toHaveBeenCalledWith(1, 'registration');
        expect(performAuthHardNavigationMock).not.toHaveBeenCalled();
        expect(sessionStoreState.updatePageLoadingState.mock.invocationCallOrder[0]).toBeLessThan(
            routerReplaceMock.mock.invocationCallOrder[0],
        );
        expect(storeAuthNotificationMock.mock.invocationCallOrder[0]).toBeLessThan(
            routerReplaceMock.mock.invocationCallOrder[0],
        );
    });

    test('performs hard navigation when client-side navigation fails after registration', async () => {
        registerMutationMock.mockResolvedValue({
            data: { Register: { showCartMergeInfo: false } },
        });
        routerReplaceMock.mockResolvedValue(false);
        const { result } = renderHook(() => useRegistration());

        await act(async () => {
            await result.current.register({} as Omit<TypeRegistrationDataInput, 'productListsUuids'>);
        });

        expect(routerReplaceMock).toHaveBeenCalledWith('/');
        expect(performAuthHardNavigationMock).toHaveBeenCalledOnce();
        expect(performAuthHardNavigationMock).toHaveBeenCalledWith('/');
    });

    test('navigates to homepage without reload and exposes feedback after registration by order', async () => {
        registerByOrderMutationMock.mockResolvedValue({
            data: { RegisterByOrder: { showCartMergeInfo: false } },
        });
        const { result } = renderHook(() => useRegistration());

        await act(async () => {
            await result.current.registerByOrder({} as Omit<TypeRegistrationByOrderInput, 'productListsUuids'>);
        });

        expect(routerReplaceMock).toHaveBeenCalledOnce();
        expect(routerReplaceMock).toHaveBeenCalledWith('/');
        expect(sessionStoreState.updatePageLoadingState).toHaveBeenCalledWith({
            isPageLoading: true,
            redirectPageType: 'homepage',
        });
        expect(storeAuthNotificationMock).toHaveBeenCalledWith(1, 'registration');
        expect(performAuthHardNavigationMock).not.toHaveBeenCalled();
        expect(sessionStoreState.updatePageLoadingState.mock.invocationCallOrder[0]).toBeLessThan(
            routerReplaceMock.mock.invocationCallOrder[0],
        );
        expect(storeAuthNotificationMock.mock.invocationCallOrder[0]).toBeLessThan(
            routerReplaceMock.mock.invocationCallOrder[0],
        );
    });
});
