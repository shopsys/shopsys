import { act, renderHook, waitFor } from '@testing-library/react';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { useRemoveFromCart } from 'utils/cart/useRemoveFromCart';
import { beforeEach, describe, expect, test, vi } from 'vitest';

const { gtmSafePushEventMock, removeFromCartMutationMock } = vi.hoisted(() => ({
    gtmSafePushEventMock: vi.fn(),
    removeFromCartMutationMock: vi.fn(),
}));

vi.mock('components/providers/AuthorizationProvider', () => ({
    useAuthorization: () => ({ canSeePrices: true }),
}));

vi.mock('components/providers/DomainConfigProvider', () => ({
    useDomainConfig: () => ({ currencyCode: 'EUR', domainId: 1, url: 'https://example.com' }),
}));

vi.mock('graphql/requests/cart/mutations/RemoveFromCartMutation.generated', () => ({
    useRemoveFromCartMutation: () => [{ fetching: false }, removeFromCartMutationMock],
}));

vi.mock('gtm/factories/getGtmChangeCartItemEvent', () => ({
    getGtmChangeCartItemEvent: (...args: unknown[]) => ({ cart: args[11] }),
}));

vi.mock('gtm/utils/getGtmMappedCart', () => ({
    getGtmMappedCart: () => ({ products: ['remaining-product'] }),
}));

vi.mock('gtm/utils/getGtmPriceBasedOnVisibility', () => ({
    getGtmPriceBasedOnVisibility: () => 1,
}));

vi.mock('gtm/utils/gtmSafePushEvent', () => ({
    gtmSafePushEvent: gtmSafePushEventMock,
}));

vi.mock('store/usePersistStore', () => ({
    usePersistStore: (selector: (state: { cartUuid: string; updateCartUuid: () => void }) => unknown) =>
        selector({ cartUuid: 'cart-uuid', updateCartUuid: vi.fn() }),
}));

vi.mock('utils/auth/useIsUserLoggedIn', () => ({
    useIsUserLoggedIn: () => false,
}));

vi.mock('utils/cart/useCurrentCart', () => ({
    useCurrentCart: () => ({ fetchCart: vi.fn() }),
}));

vi.mock('utils/useBroadcastChannel', () => ({
    dispatchBroadcastChannel: vi.fn(),
}));

describe('useRemoveFromCart', () => {
    const cartItem = {
        additionalServices: [],
        quantity: 1,
        uuid: 'cart-item-uuid',
        product: { price: { priceWithVat: '1', priceWithoutVat: '1' } },
    };

    beforeEach(() => {
        vi.clearAllMocks();
    });

    test('sends the remaining cart in the GTM event', async () => {
        removeFromCartMutationMock.mockResolvedValue({
            data: { RemoveFromCart: { items: [{}], promoCodes: [], uuid: 'cart-uuid' } },
        });
        const { result } = renderHook(() => useRemoveFromCart(GtmProductListNameType.cart));

        await act(async () => {
            await result.current.removeFromCart(cartItem as never);
        });

        await waitFor(() => {
            expect(gtmSafePushEventMock).toHaveBeenCalledWith({ cart: { products: ['remaining-product'] } });
        });
    });

    test('sends an undefined cart when the removal empties the cart', async () => {
        removeFromCartMutationMock.mockResolvedValue({
            data: { RemoveFromCart: { items: [], promoCodes: [], uuid: 'cart-uuid' } },
        });
        const { result } = renderHook(() => useRemoveFromCart(GtmProductListNameType.cart));

        await act(async () => {
            await result.current.removeFromCart(cartItem as never);
        });

        await waitFor(() => {
            expect(gtmSafePushEventMock).toHaveBeenCalledWith({ cart: undefined });
        });
    });
});
