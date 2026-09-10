import { TypeCartItemFragment } from 'graphql/requests/cart/fragments/CartItemFragment.generated';
import { mapGtmServiceCartItems } from 'gtm/mappers/mapGtmServiceCartItems';
import { describe, expect, test } from 'vitest';

const assemblyService = {
    __typename: 'AdditionalService',
    id: 11,
    uuid: 'assembly-uuid',
    name: 'Assembly',
    catnum: 'SERVICE-ASSEMBLY',
    description: null,
    deliveryDaysExtension: 1,
    price: {
        __typename: 'Price',
        priceWithVat: '121.00',
        priceWithoutVat: '100.00',
        vatAmount: '21.00',
    },
    mainImage: null,
};

const giftWrappingService = {
    __typename: 'AdditionalService',
    id: 12,
    uuid: 'gift-wrapping-uuid',
    name: 'Gift wrapping',
    catnum: 'SERVICE-GIFT-WRAPPING',
    description: null,
    deliveryDaysExtension: null,
    price: {
        __typename: 'Price',
        priceWithVat: '60.50',
        priceWithoutVat: '50.00',
        vatAmount: '10.50',
    },
    mainImage: null,
};

const createCartItem = (productId: number, quantity: number, additionalServices: unknown[]) =>
    ({
        quantity,
        product: { id: productId },
        additionalServices,
    }) as unknown as TypeCartItemFragment;

describe('mapGtmServiceCartItems test', () => {
    test('services are mapped with the spec field set', () => {
        const serviceCartItems = mapGtmServiceCartItems([createCartItem(101, 2, [assemblyService])]);

        expect(serviceCartItems).toStrictEqual([
            {
                id: 11,
                sku: 'SERVICE-ASSEMBLY',
                productType: 'service',
                name: 'Assembly',
                sourceProductIds: [101],
                priceWithoutVat: 100,
                priceWithVat: 121,
                quantity: 2,
            },
        ]);
    });

    test('the same service on two cart items is aggregated into one entry', () => {
        const serviceCartItems = mapGtmServiceCartItems([
            createCartItem(101, 2, [assemblyService, giftWrappingService]),
            createCartItem(102, 3, [assemblyService]),
        ]);

        expect(serviceCartItems).toHaveLength(2);
        expect(serviceCartItems[0]).toMatchObject({
            id: 11,
            sourceProductIds: [101, 102],
            quantity: 5,
        });
        expect(serviceCartItems[1]).toMatchObject({
            id: 12,
            sourceProductIds: [101],
            quantity: 2,
        });
    });

    test('cart items without services produce no entries', () => {
        expect(mapGtmServiceCartItems([createCartItem(101, 1, [])])).toStrictEqual([]);
    });
});
