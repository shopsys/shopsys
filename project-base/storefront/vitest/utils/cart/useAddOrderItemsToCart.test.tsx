import { act, renderHook } from '@testing-library/react';
import { TypeCartFragment } from 'graphql/requests/cart/fragments/CartFragment.generated';
import { TypeCartItemFragment } from 'graphql/requests/cart/fragments/CartItemFragment.generated';
import { TypeAvailabilityStatusEnum, TypeCartItemTypeEnum } from 'graphql/types';
import { ReactElement } from 'react';
import { useAddOrderItemsToCart } from 'utils/cart/useAddOrderItemsToCart';
import { beforeEach, describe, expect, test, vi } from 'vitest';

const mocks = vi.hoisted(() => ({
    mutate: vi.fn(),
    pushEvent: vi.fn(),
    navigate: vi.fn(),
    updateCartUuid: vi.fn(),
    updatePortalContent: vi.fn(),
    updatePageLoadingState: vi.fn(),
    storeCurrentFocus: vi.fn(),
    cart: null as TypeCartFragment | null,
    canSeePrices: true,
}));

vi.mock('components/providers/AuthorizationProvider', () => ({
    useAuthorization: () => ({ canSeePrices: mocks.canSeePrices }),
}));
vi.mock('components/providers/DomainConfigProvider', () => ({
    useDomainConfig: () => ({ currencyCode: 'EUR', domainId: 1, url: 'https://example.com' }),
}));
vi.mock('graphql/requests/cart/mutations/AddOrderItemsToCartMutation.generated', () => ({
    useAddOrderItemsToCartMutation: () => [{}, mocks.mutate],
}));
vi.mock('next/router', () => ({ useRouter: () => ({ push: mocks.navigate }) }));
vi.mock('next/dynamic', () => ({ default: () => () => null }));
vi.mock('store/usePersistStore', () => ({
    usePersistStore: (selector: (state: typeof mocks) => unknown) => selector(mocks),
}));
vi.mock('store/useSessionStore', () => ({
    useSessionStore: (selector: (state: typeof mocks) => unknown) => selector(mocks),
}));
vi.mock('utils/auth/useIsUserLoggedIn', () => ({ useIsUserLoggedIn: () => false }));
vi.mock('utils/cart/useCurrentCart', () => ({ useCurrentCart: () => ({ cart: mocks.cart }) }));
vi.mock('utils/staticUrls/getInternationalizedStaticUrls', () => ({
    getInternationalizedStaticUrls: () => ['/cart'],
}));
vi.mock('utils/useBroadcastChannel', () => ({ dispatchBroadcastChannel: vi.fn() }));
vi.mock('gtm/utils/gtmSafePushEvent', () => ({ gtmSafePushEvent: mocks.pushEvent }));

const createItem = (uuid: string, quantity: number, type = TypeCartItemTypeEnum.Product): TypeCartItemFragment =>
    ({
        uuid,
        quantity,
        type,
        additionalServices: [],
        product: {
            __typename: 'RegularProduct',
            id: 1,
            catalogNumber: 'SKU-1',
            fullName: 'Product',
            slug: '/product',
            flags: [],
            availability: { status: TypeAvailabilityStatusEnum.InStock },
            price: { priceWithoutVat: '100', priceWithVat: '121', vatAmount: '21' },
            giftPrice: { priceWithoutVat: '10', priceWithVat: '12.1', vatAmount: '2.1' },
        },
    }) as unknown as TypeCartItemFragment;

const createCart = (items: TypeCartItemFragment[], notAddedProducts: { fullName: string }[] = []): TypeCartFragment =>
    ({
        uuid: 'cart',
        items,
        promoCodes: [],
        totalItemsPrice: { priceWithoutVat: '220', priceWithVat: '266.2' },
        modifications: { multipleAddedProductModifications: { notAddedProducts } },
    }) as unknown as TypeCartFragment;

type MergePopupProps = {
    mergeOrderItemsWithCurrentCart: (
        orderUuid: string,
        orderUrlHash: string | null,
        shouldMerge: boolean,
    ) => Promise<void>;
};

const repeatOrder = async (shouldMerge?: boolean) => {
    const { result } = renderHook(() => useAddOrderItemsToCart());

    await act(async () => {
        await result.current('order', 'order-hash');
    });

    if (shouldMerge !== undefined) {
        expect(mocks.mutate).not.toHaveBeenCalled();
        expect(mocks.pushEvent).not.toHaveBeenCalled();
        const popup = mocks.updatePortalContent.mock.calls[0][0] as ReactElement<MergePopupProps>;
        await act(async () => {
            await popup.props.mergeOrderItemsWithCurrentCart('order', 'order-hash', shouldMerge);
        });
    }

    await act(async () => {
        await vi.dynamicImportSettled();
    });
};

describe('repeat order GTM event', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        mocks.mutate.mockReset();
        mocks.cart = null;
        mocks.canSeePrices = true;
    });

    test('reports the returned products and gifts once when filling an empty cart', async () => {
        mocks.mutate.mockResolvedValue({
            data: {
                AddOrderItemsToCart: createCart([
                    createItem('product', 2),
                    createItem('gift', 2, TypeCartItemTypeEnum.ProductGift),
                ]),
            },
        });

        await repeatOrder();

        expect(mocks.pushEvent).toHaveBeenCalledExactlyOnceWith(
            expect.objectContaining({
                event: 'ec.add_to_cart',
                ecommerce: expect.objectContaining({
                    listName: 'repeat order',
                    currencyCode: 'EUR',
                    valueWithoutVat: 220,
                    valueWithVat: 266.2,
                    arePricesHidden: false,
                    products: [
                        expect.objectContaining({ id: 1, sku: 'SKU-1', quantity: 2 }),
                        expect.objectContaining({ id: 'gift-1', sku: 'gift-SKU-1', quantity: 2, priceWithVat: 12.1 }),
                    ],
                }),
                cart: expect.objectContaining({ products: expect.any(Array) }),
                _clear: true,
            }),
        );
        expect(mocks.navigate).toHaveBeenCalledWith('/cart');
    });

    test('reports only positive quantity changes when merging, including gifts and attached services', async () => {
        const product = createItem('product', 2);
        product.additionalServices = [
            {
                id: 11,
                uuid: 'service',
                name: 'Assembly',
                catnum: 'SERVICE',
                price: { priceWithoutVat: '5', priceWithVat: '6.05', vatAmount: '1.05' },
            },
        ] as TypeCartItemFragment['additionalServices'];
        const gift = createItem('gift', 1, TypeCartItemTypeEnum.ProductGift);
        const unchanged = createItem('unchanged', 3);
        unchanged.product = { ...unchanged.product, id: 2, catalogNumber: 'SKU-2' };
        mocks.cart = createCart([product, gift, unchanged]);
        mocks.mutate.mockResolvedValue({
            data: {
                AddOrderItemsToCart: createCart([{ ...product, quantity: 4 }, { ...gift, quantity: 2 }, unchanged]),
            },
        });

        await repeatOrder(true);

        expect(mocks.pushEvent).toHaveBeenCalledTimes(1);
        const event = mocks.pushEvent.mock.calls[0][0];
        expect(event.ecommerce.products).toEqual([
            expect.objectContaining({ id: 1, quantity: 2 }),
            expect.objectContaining({ id: 'gift-1', quantity: 1 }),
            expect.objectContaining({ id: 11, productType: 'service', quantity: 2 }),
        ]);
        expect(event.ecommerce.valueWithoutVat).toBe(220);
        expect(event.ecommerce.valueWithVat).toBeCloseTo(266.2);
        expect(event.cart.products).toEqual([
            expect.objectContaining({ id: 1, quantity: 4 }),
            expect.objectContaining({ id: 'gift-1', quantity: 2 }),
            expect.objectContaining({ id: 2, quantity: 3 }),
            expect.objectContaining({ id: 11, quantity: 4 }),
        ]);
        expect(mocks.mutate).toHaveBeenCalledWith({
            input: {
                orderUuid: 'order',
                orderUrlHash: 'order-hash',
                cartUuid: 'cart',
                shouldMerge: true,
            },
        });
    });

    test('reports the whole replacement cart without subtracting the old cart', async () => {
        mocks.cart = createCart([createItem('product', 4)]);
        mocks.mutate.mockResolvedValue({ data: { AddOrderItemsToCart: createCart([createItem('product', 1)]) } });

        await repeatOrder(false);

        expect(mocks.pushEvent).toHaveBeenCalledExactlyOnceWith(
            expect.objectContaining({
                ecommerce: expect.objectContaining({ products: [expect.objectContaining({ id: 1, quantity: 1 })] }),
            }),
        );
        expect(mocks.mutate).toHaveBeenCalledWith({
            input: {
                orderUuid: 'order',
                orderUrlHash: 'order-hash',
                cartUuid: null,
                shouldMerge: false,
            },
        });
    });

    test('reports only available items when part of the order cannot be added', async () => {
        mocks.mutate.mockResolvedValue({
            data: {
                AddOrderItemsToCart: createCart([createItem('product', 1)], [{ fullName: 'Unavailable product' }]),
            },
        });

        await repeatOrder();

        expect(mocks.pushEvent).toHaveBeenCalledExactlyOnceWith(
            expect.objectContaining({
                ecommerce: expect.objectContaining({ products: [expect.objectContaining({ id: 1, quantity: 1 })] }),
            }),
        );
        expect(mocks.navigate).not.toHaveBeenCalled();
        expect(mocks.updatePortalContent.mock.calls[0][0].props.notAddedProductNames).toEqual(['Unavailable product']);
    });

    test('preserves hidden prices in the event', async () => {
        mocks.canSeePrices = false;
        const item = createItem('product', 1);
        item.product.price = { ...item.product.price, priceWithoutVat: '***', priceWithVat: '***' };
        mocks.mutate.mockResolvedValue({ data: { AddOrderItemsToCart: createCart([item]) } });

        await repeatOrder();

        expect(mocks.pushEvent).toHaveBeenCalledExactlyOnceWith(
            expect.objectContaining({
                ecommerce: expect.objectContaining({
                    arePricesHidden: true,
                    valueWithoutVat: null,
                    valueWithVat: null,
                    products: [expect.objectContaining({ priceWithoutVat: null, priceWithVat: null })],
                }),
            }),
        );
    });

    test.each([
        0, 1, 2,
    ])('does not send an add event when the merged quantity is %i and was already two', async (quantity) => {
        mocks.cart = createCart([createItem('product', 2)]);
        mocks.mutate.mockResolvedValue({
            data: { AddOrderItemsToCart: createCart(quantity ? [createItem('product', quantity)] : []) },
        });

        await repeatOrder(true);

        expect(mocks.pushEvent).not.toHaveBeenCalled();
    });

    test.each([
        { error: new Error('Mutation failed') },
        { data: undefined },
    ])('does not report an unsuccessful mutation: %j', async (response) => {
        mocks.mutate.mockResolvedValue(response);

        await repeatOrder();

        expect(mocks.pushEvent).not.toHaveBeenCalled();
    });

    test('does not send an empty event when no order item can be added', async () => {
        mocks.mutate.mockResolvedValue({
            data: { AddOrderItemsToCart: createCart([], [{ fullName: 'Unavailable product' }]) },
        });

        await repeatOrder();

        expect(mocks.pushEvent).not.toHaveBeenCalled();
    });
});
