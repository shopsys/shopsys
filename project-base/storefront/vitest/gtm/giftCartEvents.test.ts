import { TypeCartFragment } from 'graphql/requests/cart/fragments/CartFragment.generated';
import { TypeCartItemFragment } from 'graphql/requests/cart/fragments/CartItemFragment.generated';
import { TypeAddToCartMutation } from 'graphql/requests/cart/mutations/AddToCartMutation.generated';
import { TypeAvailabilityStatusEnum, TypeCartItemTypeEnum } from 'graphql/types';
import { GtmEventType } from 'gtm/enums/GtmEventType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { onGtmChangeCartItemEventHandler } from 'gtm/handlers/onGtmChangeCartItemEventHandler';
import { onGtmRemoveFromCartEventHandler } from 'gtm/handlers/onGtmRemoveFromCartEventHandler';
import { gtmSafePushEvent } from 'gtm/utils/gtmSafePushEvent';
import { DomainConfigType } from 'utils/domain/domainConfig';
import { describe, expect, test, vi } from 'vitest';

vi.mock('gtm/utils/gtmSafePushEvent', () => ({ gtmSafePushEvent: vi.fn() }));
vi.mock('utils/staticUrls/getInternationalizedStaticUrls', () => ({
    getInternationalizedStaticUrls: () => ['/abandoned-cart/cart'],
}));

const createCartItem = (uuid: string, quantity: number, type = TypeCartItemTypeEnum.Product): TypeCartItemFragment =>
    ({
        uuid,
        quantity,
        type,
        additionalServices: [],
        product: {
            __typename: 'RegularProduct',
            id: 1,
            catalogNumber: 'SKU-1',
            slug: '/product',
            flags: [],
            availability: { status: TypeAvailabilityStatusEnum.InStock },
            price: { priceWithoutVat: '100', priceWithVat: '121', vatAmount: '21' },
            giftPrice: { priceWithoutVat: '10', priceWithVat: '12.1', vatAmount: '2.1' },
        },
    }) as unknown as TypeCartItemFragment;

const sendChange = (previousItems: TypeCartItemFragment[], updatedItems: TypeCartItemFragment[]) => {
    const product = updatedItems[0];
    onGtmChangeCartItemEventHandler(
        previousItems[0]?.quantity ?? 0,
        true,
        { addProductResult: { addedQuantity: product.quantity } } as TypeAddToCartMutation['AddToCart'],
        product,
        {
            items: updatedItems,
            promoCodes: [],
            uuid: 'cart',
            totalItemsPrice: { priceWithoutVat: '110', priceWithVat: '133.1' },
        } as unknown as TypeCartFragment,
        { url: 'https://example.com', currencyCode: 'EUR' } as DomainConfigType,
        undefined,
        GtmProductListNameType.product_detail,
        false,
        false,
        previousItems,
    );
};

describe('gift cart events', () => {
    test.each([
        { before: 0, after: 1, event: GtmEventType.add_to_cart },
        { before: 2, after: 3, event: GtmEventType.add_to_cart },
        { before: 3, after: 2, event: GtmEventType.remove_from_cart },
    ])('reports only changed quantities when moving from $before to $after', ({ before, after, event }) => {
        const previousItems = before
            ? [createCartItem('product', before), createCartItem('gift', before, TypeCartItemTypeEnum.ProductGift)]
            : [];
        const updatedItems = [
            createCartItem('product', after),
            createCartItem('gift', after, TypeCartItemTypeEnum.ProductGift),
        ];

        sendChange(previousItems, updatedItems);

        expect(gtmSafePushEvent).toHaveBeenCalledWith(
            expect.objectContaining({
                event,
                ecommerce: expect.objectContaining({
                    valueWithoutVat: 110,
                    valueWithVat: 133.1,
                    products: [
                        expect.objectContaining({ id: 1, sku: 'SKU-1', productType: 'product', quantity: 1 }),
                        expect.objectContaining({
                            id: 'gift-1',
                            sku: 'gift-SKU-1',
                            productType: 'gift',
                            quantity: 1,
                            priceWithVat: 12.1,
                        }),
                    ],
                }),
                cart: expect.objectContaining({
                    products: [
                        expect.objectContaining({ id: 1, sku: 'SKU-1', quantity: after }),
                        expect.objectContaining({ id: 'gift-1', sku: 'gift-SKU-1', quantity: after }),
                    ],
                }),
            }),
        );
    });

    test('does not report an unchanged gift again', () => {
        const gift = createCartItem('gift', 1, TypeCartItemTypeEnum.ProductGift);

        sendChange([createCartItem('product', 1), gift], [createCartItem('product', 2), gift]);

        expect(gtmSafePushEvent).toHaveBeenCalledWith(
            expect.objectContaining({
                ecommerce: expect.objectContaining({
                    valueWithVat: 121,
                    products: [expect.objectContaining({ id: 1, quantity: 1 })],
                }),
            }),
        );
    });

    test('includes a gift that disappeared after reducing the product quantity', () => {
        sendChange(
            [createCartItem('product', 2), createCartItem('gift', 1, TypeCartItemTypeEnum.ProductGift)],
            [createCartItem('product', 1)],
        );

        expect(gtmSafePushEvent).toHaveBeenCalledWith(
            expect.objectContaining({
                event: GtmEventType.remove_from_cart,
                ecommerce: expect.objectContaining({
                    products: [
                        expect.objectContaining({ id: 1, quantity: 1 }),
                        expect.objectContaining({ id: 'gift-1', sku: 'gift-SKU-1', productType: 'gift', quantity: 1 }),
                    ],
                }),
            }),
        );
    });

    test.each([
        { withoutVat: '0', withVat: '0', totalWithoutVat: 0, totalWithVat: 0 },
        { withoutVat: '10', withVat: '12.1', totalWithoutVat: 20, totalWithVat: 24.2 },
        { withoutVat: '***', withVat: '***', totalWithoutVat: null, totalWithVat: null },
    ])('removes gifts using their actual price $withVat', ({ withoutVat, withVat, totalWithoutVat, totalWithVat }) => {
        const gift = createCartItem('gift', 2, TypeCartItemTypeEnum.ProductGift);
        gift.product.giftPrice.priceWithoutVat = withoutVat;
        gift.product.giftPrice.priceWithVat = withVat;

        onGtmRemoveFromCartEventHandler(
            gift,
            'EUR',
            undefined,
            GtmProductListNameType.cart,
            'https://example.com',
            withVat === '***',
        );

        expect(gtmSafePushEvent).toHaveBeenCalledWith(
            expect.objectContaining({
                event: GtmEventType.remove_from_cart,
                ecommerce: expect.objectContaining({
                    valueWithoutVat: totalWithoutVat,
                    valueWithVat: totalWithVat,
                    products: [
                        expect.objectContaining({ id: 'gift-1', sku: 'gift-SKU-1', productType: 'gift', quantity: 2 }),
                    ],
                }),
            }),
        );
    });
});
