import { act, renderHook, waitFor } from '@testing-library/react';
import { GtmEventType } from 'gtm/enums/GtmEventType';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { useAddToCart } from 'utils/cart/useAddToCart';
import { beforeEach, describe, expect, test, vi } from 'vitest';

const { addToCartMutationMock, gtmSafePushEventMock } = vi.hoisted(() => ({
    addToCartMutationMock: vi.fn(),
    gtmSafePushEventMock: vi.fn(),
}));

vi.mock('components/providers/AuthorizationProvider', () => ({
    useAuthorization: () => ({ canSeePrices: true }),
}));

vi.mock('components/providers/DomainConfigProvider', () => ({
    useDomainConfig: () => ({ currencyCode: 'EUR', domainId: 1, url: 'https://example.com' }),
}));

vi.mock('graphql/requests/cart/mutations/AddToCartMutation.generated', () => ({
    useAddToCartMutation: () => [{ fetching: false }, addToCartMutationMock],
}));

vi.mock('gtm/factories/getGtmChangeCartItemEvent', () => ({
    getGtmChangeCartItemEvent: (event: GtmEventType) => ({ event }),
}));

vi.mock('gtm/utils/getGtmMappedCart', () => ({
    getGtmMappedCart: () => null,
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
    useCurrentCart: () => ({
        cart: {
            items: [{ product: { uuid: 'product-uuid' }, quantity: 1 }],
        },
    }),
}));

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({ t: (key: string) => key }),
}));

vi.mock('utils/toasts/showInfoMessage', () => ({
    showInfoMessage: vi.fn(),
}));

vi.mock('utils/useBroadcastChannel', () => ({
    dispatchBroadcastChannel: vi.fn(),
}));

describe('useAddToCart', () => {
    const createAddToCartResult = (quantity: number) => ({
        data: {
            AddToCart: {
                addProductResult: {
                    addedQuantity: quantity,
                    cartItem: {
                        product: { price: { priceWithVat: '1', priceWithoutVat: '1' }, uuid: 'product-uuid' },
                        quantity,
                    },
                    notOnStockQuantity: 0,
                },
                cart: {
                    items: [],
                    promoCodes: [],
                    uuid: 'cart-uuid',
                },
            },
        },
    });

    beforeEach(() => {
        vi.clearAllMocks();
        addToCartMutationMock
            .mockResolvedValueOnce(createAddToCartResult(2))
            .mockResolvedValueOnce(createAddToCartResult(1));
    });

    test('uses the latest confirmed quantity for sequential absolute changes without a rerender', async () => {
        const { result } = renderHook(() => useAddToCart(GtmMessageOriginType.cart, GtmProductListNameType.cart));

        await act(async () => {
            await result.current.addToCart('product-uuid', 2, 0, true);
            await result.current.addToCart('product-uuid', 1, 0, true);
        });

        await waitFor(() => {
            expect(gtmSafePushEventMock).toHaveBeenCalledTimes(2);
        });
        expect(gtmSafePushEventMock).toHaveBeenNthCalledWith(
            1,
            expect.objectContaining({ event: GtmEventType.add_to_cart }),
        );
        expect(gtmSafePushEventMock).toHaveBeenNthCalledWith(
            2,
            expect.objectContaining({ event: GtmEventType.remove_from_cart }),
        );
    });
});
