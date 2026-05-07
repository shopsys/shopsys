import { TypeCartItemFragment } from 'graphql/requests/cart/fragments/CartItemFragment.generated';
import { TypeCartItemTypeEnum, TypeParameterTypeEnum } from 'graphql/types';
import { mapGtmCartItemType } from 'gtm/mappers/mapGtmCartItemType';
import { describe, expect, test } from 'vitest';

const cartItem = {
    __typename: 'CartItem',
    uuid: 'f2a0464a-b2d3-4d8c-8326-45d1f0a5db29',
    quantity: 2,
    type: TypeCartItemTypeEnum.Product,
    freeQuantity: 0,
    product: {
        __typename: 'Variant',
        id: 195,
        uuid: '7a97ed51-a102-49be-b395-aab7e86891b7',
        slug: '/product-variant',
        fullName: 'Product variant',
        catalogNumber: '123456',
        isInquiryType: false,
        stockQuantity: 5,
        isAllowedNegativeStock: false,
        promotionBuyQuantity: null,
        promotionFreeQuantity: null,
        availableStoresCount: null,
        vatPercent: '21',
        mainVariant: {
            slug: '/main-variant',
        },
        flags: [],
        mainImage: null,
        availability: {
            name: 'In stock',
        },
        price: {
            priceWithoutVat: '100',
            priceWithVat: '121',
            vatAmount: '21',
        },
        giftPrice: {
            priceWithoutVat: '0',
            priceWithVat: '0',
            vatAmount: '0',
        },
        unit: {
            name: 'pcs',
        },
        brand: {
            name: 'Brand',
        },
        categories: [{ name: 'Category' }],
        parameters: [
            {
                __typename: 'Parameter',
                uuid: 'color-parameter',
                name: 'color',
                type: TypeParameterTypeEnum.Color,
                group: null,
                unit: null,
                values: [
                    {
                        uuid: 'black-value',
                        text: 'black',
                        rgbHex: '#000000',
                        colorIcon: null,
                    },
                ],
            },
            {
                __typename: 'Parameter',
                uuid: 'size-parameter',
                name: 'size',
                type: TypeParameterTypeEnum.Checkbox,
                group: null,
                unit: {
                    __typename: 'Unit',
                    name: 'cm',
                },
                values: [
                    {
                        uuid: 'size-value',
                        text: '100x22',
                        rgbHex: null,
                        colorIcon: null,
                    },
                ],
            },
        ],
    },
} as unknown as TypeCartItemFragment;

describe('mapGtmCartItemType', () => {
    test('should add variant parameters for variant cart items', () => {
        const result = mapGtmCartItemType(cartItem, 'https://example.com', 0);

        expect(result).toMatchObject({
            id: 195,
            name: 'Product variant',
            quantity: 2,
            listIndex: 1,
            variant: 'color: black; size: 100x22 cm',
        });
    });

    test('should keep explicit quantity override', () => {
        const result = mapGtmCartItemType(cartItem, 'https://example.com', undefined, 1);

        expect(result.quantity).toBe(1);
    });

    test('should not add variant parameters for regular product cart items', () => {
        const result = mapGtmCartItemType(
            {
                ...cartItem,
                product: {
                    ...cartItem.product,
                    __typename: 'RegularProduct',
                },
            } as unknown as TypeCartItemFragment,
            'https://example.com',
        );

        expect(result.variant).toBeUndefined();
    });
});
