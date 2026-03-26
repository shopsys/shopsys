import { act, renderHook } from '@testing-library/react';
import { useLoginAfterPasswordRecovery } from 'utils/auth/useLogin';
import { beforeEach, describe, expect, test, vi } from 'vitest';

const mockUpdateAuthLoadingState = vi.fn();
const mockUpdateUserEntryState = vi.fn();
const mockUpdateCartUuid = vi.fn();
const mockUpdateProductListUuids = vi.fn();
const mockUpdatePageLoadingState = vi.fn();
const mockSetTokensToCookies = vi.fn();
const mockDispatchBroadcastChannel = vi.fn();

let mockDomainUrl = 'http://127.0.0.1:8000';
let mockDomainDefaultLocale = 'cs';
let mockDomainId = 1;

vi.mock('next/router', () => ({
    useRouter: () => ({}),
}));

vi.mock('components/providers/DomainConfigProvider', () => ({
    useDomainConfig: () => ({
        domainId: mockDomainId,
        url: mockDomainUrl,
        defaultLocale: mockDomainDefaultLocale,
    }),
}));

vi.mock('store/usePersistStore', () => ({
    usePersistStore: (
        selector: (store: {
            updateAuthLoadingState: typeof mockUpdateAuthLoadingState;
            updateUserEntryState: typeof mockUpdateUserEntryState;
            updateCartUuid: typeof mockUpdateCartUuid;
            updateProductListUuids: typeof mockUpdateProductListUuids;
        }) => unknown,
    ) =>
        selector({
            updateAuthLoadingState: mockUpdateAuthLoadingState,
            updateUserEntryState: mockUpdateUserEntryState,
            updateCartUuid: mockUpdateCartUuid,
            updateProductListUuids: mockUpdateProductListUuids,
        }),
}));

vi.mock('store/useSessionStore', () => ({
    useSessionStore: (selector: (store: { updatePageLoadingState: typeof mockUpdatePageLoadingState }) => unknown) =>
        selector({ updatePageLoadingState: mockUpdatePageLoadingState }),
}));

vi.mock('utils/auth/setTokensToCookies', () => ({
    setTokensToCookies: (...args: unknown[]) => mockSetTokensToCookies(...args),
}));

vi.mock('utils/useBroadcastChannel', () => ({
    dispatchBroadcastChannel: (...args: unknown[]) => mockDispatchBroadcastChannel(...args),
}));

describe('useLoginAfterPasswordRecovery', () => {
    const originalLocation = window.location;

    beforeEach(() => {
        vi.clearAllMocks();
        mockDomainId = 1;
        mockDomainUrl = 'http://127.0.0.1:8000';
        mockDomainDefaultLocale = 'cs';

        Object.defineProperty(window, 'location', {
            writable: true,
            value: { ...originalLocation, href: 'https://test.example.com/' },
        });
    });

    test('redirects to homepage without locale prefix on default-locale pathless domain', () => {
        const { result } = renderHook(() => useLoginAfterPasswordRecovery());

        act(() => {
            result.current(false, 'access-token', 'refresh-token');
        });

        expect(mockSetTokensToCookies).toHaveBeenCalledWith(
            'access-token',
            'refresh-token',
            expect.objectContaining({
                url: 'http://127.0.0.1:8000',
                defaultLocale: 'cs',
            }),
        );
        expect(mockDispatchBroadcastChannel).toHaveBeenCalledWith('reloadPage', 1);
        expect(mockUpdatePageLoadingState).toHaveBeenCalledWith({ isPageLoading: true, redirectPageType: 'homepage' });
        expect(window.location.href).toBe('/');
    });

    test('keeps locale prefix when redirecting on locale-path domain', () => {
        mockDomainId = 3;
        mockDomainUrl = 'http://127.0.0.1:8000/sk';
        mockDomainDefaultLocale = 'sk';

        const { result } = renderHook(() => useLoginAfterPasswordRecovery());

        act(() => {
            result.current(false, 'access-token', 'refresh-token');
        });

        expect(mockDispatchBroadcastChannel).toHaveBeenCalledWith('reloadPage', 3);
        expect(window.location.href).toBe('/sk');
    });
});
