import { act, renderHook } from '@testing-library/react';
import { useSessionStore } from 'store/useSessionStore';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import { useOrderPagesAccess } from 'utils/cart/useOrderPagesAccess';
import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest';

const { state, router, fetchCartMock, useCartQueryMock } = vi.hoisted(() => ({
    state: {
        cartUuid: 'guest-cart' as string | null,
        isUserLoggedIn: false,
        packeteryPickupPoint: null,
    },
    router: { replace: vi.fn() },
    fetchCartMock: vi.fn(),
    useCartQueryMock: vi.fn(),
}));

vi.mock('components/providers/AuthorizationProvider', () => ({
    useAuthorization: () => ({ canCreateOrder: true }),
}));

vi.mock('components/providers/DomainConfigProvider', () => ({
    useDomainConfig: () => ({ url: 'https://example.com' }),
}));

vi.mock('next/router', () => ({ useRouter: () => router }));

vi.mock('utils/staticUrls/getInternationalizedStaticUrls', () => ({
    getInternationalizedStaticUrls: (urls: string[]) => urls,
}));

vi.mock('graphql/requests/cart/queries/CartQuery.generated', () => ({
    useCartQuery: useCartQueryMock,
}));

vi.mock('store/usePersistStore', () => ({
    usePersistStore: (selector: (store: typeof state) => unknown) => selector(state),
}));

vi.mock('utils/auth/useIsUserLoggedIn', () => ({
    useIsUserLoggedIn: () => state.isUserLoggedIn,
}));

const cart = { uuid: 'guest-cart', items: [{ uuid: 'cart-item' }], transport: {}, payment: {} };
const initialSessionState = useSessionStore.getState();

describe('cart availability during login', () => {
    beforeEach(() => {
        state.cartUuid = 'guest-cart';
        state.isUserLoggedIn = false;
        useSessionStore.setState(initialSessionState, true);
        useCartQueryMock.mockReturnValue([{ data: { cart }, fetching: false }, fetchCartMock]);
    });

    afterEach(() => {
        act(() => useSessionStore.setState(initialSessionState, true));
    });

    test('does not expose a cleared guest identity as an empty cart while login finishes', () => {
        const { result, rerender } = renderHook(() => useCurrentCart());
        expect(result.current.cart).toBe(cart);

        act(() => {
            useSessionStore.getState().setCartStale(true);
            state.cartUuid = null;
        });
        rerender();

        expect(result.current.cart).toBeUndefined();
        expect(result.current.isCartFetchingOrUnavailable).toBe(true);
    });

    test('pauses cart requests and refetches while the customer identity is changing', () => {
        useSessionStore.getState().setCartStale(true);
        const { result } = renderHook(() => useCurrentCart());

        act(() => result.current.fetchCart());

        expect(useCartQueryMock).toHaveBeenLastCalledWith(expect.objectContaining({ pause: true }));
        expect(fetchCartMock).not.toHaveBeenCalled();
    });

    test('keeps checkout open instead of redirecting to an empty cart during login', () => {
        const { result, rerender } = renderHook(() => useOrderPagesAccess('contact-information'));
        expect(result.current).toBe(true);

        act(() => {
            useSessionStore.getState().setCartStale(true);
            state.cartUuid = null;
        });
        rerender();

        expect(router.replace).not.toHaveBeenCalled();
    });

    test('still redirects a genuinely empty guest cart to the cart page', () => {
        state.cartUuid = null;
        const { result } = renderHook(() => useOrderPagesAccess('contact-information'));

        expect(result.current).toBe(false);
        expect(router.replace).toHaveBeenCalledWith('/cart');
    });

    test('loads the customer cart after a new document resets the temporary state', () => {
        useSessionStore.getState().setCartStale(true);
        useSessionStore.setState(initialSessionState, true);
        state.cartUuid = null;
        state.isUserLoggedIn = true;
        const { result } = renderHook(() => useCurrentCart());

        expect(useCartQueryMock).toHaveBeenLastCalledWith(expect.objectContaining({ pause: false }));
        expect(result.current.cart).toBe(cart);
        expect(result.current.isCartFetchingOrUnavailable).toBe(false);
    });
});
