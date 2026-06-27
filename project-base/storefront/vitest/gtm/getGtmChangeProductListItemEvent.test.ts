import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { TypeAvailabilityStatusEnum } from 'graphql/types';
import { GtmEventType } from 'gtm/enums/GtmEventType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { getGtmChangeProductListItemEvent } from 'gtm/factories/getGtmChangeProductListItemEvent';
import { describe, expect, test } from 'vitest';

const listedProduct = {
    __typename: 'RegularProduct',
    id: 42,
    uuid: 'f7888ef5-ae16-4f5c-b98d-4a6c947a9f71',
    slug: '/test-product',
    fullName: 'Test product',
    stockQuantity: 10,
    isAllowedNegativeStock: false,
    isSellingDenied: false,
    isCurrentlyOutOfStock: false,
    flags: [{ __typename: 'Flag', uuid: 'flag-uuid', name: 'Action', rgbColor: '#ff0000' }],
    mainImage: { __typename: 'Image', url: 'https://example.com/image.jpg' },
    price: {
        __typename: 'ProductPrice',
        priceWithVat: '121',
        priceWithoutVat: '100',
        vatAmount: '21',
        isPriceFrom: false,
        percentageDiscount: null,
        basicPrice: {
            __typename: 'Price',
            priceWithVat: '121',
        },
    },
    availability: { __typename: 'Availability', name: 'In stock', status: TypeAvailabilityStatusEnum.InStock },
    availableStoresCount: 1,
    catalogNumber: 'TEST-42',
    brand: { __typename: 'Brand', name: 'Test brand' },
    categories: [{ __typename: 'Category', name: 'Test category' }],
    isMainVariant: false,
    isInquiryType: false,
    unit: { __typename: 'Unit', name: 'pcs' },
} satisfies TypeListedProductFragment;

describe('getGtmChangeProductListItemEvent', () => {
    test('should create add to wishlist event with mapped product data', () => {
        const result = getGtmChangeProductListItemEvent(
            GtmEventType.add_to_wishlist,
            listedProduct,
            2,
            'CZK',
            GtmProductListNameType.category_detail,
            'https://example.com',
            false,
        );

        expect(result).toEqual({
            event: GtmEventType.add_to_wishlist,
            ecommerce: {
                listName: GtmProductListNameType.category_detail,
                currencyCode: 'CZK',
                valueWithoutVat: 100,
                valueWithVat: 121,
                products: [
                    {
                        id: 42,
                        name: 'Test product',
                        availability: 'In stock',
                        imageUrl: 'https://example.com/image.jpg',
                        flags: ['Action'],
                        priceWithoutVat: 100,
                        priceWithVat: 121,
                        vatAmount: 21,
                        sku: 'TEST-42',
                        url: 'https://example.com/test-product',
                        brand: 'Test brand',
                        categories: ['Test category'],
                        quantity: 1,
                        listIndex: 3,
                    },
                ],
                arePricesHidden: false,
            },
            _clear: true,
        });
    });

    test('should create remove from wishlist event without list index', () => {
        const result = getGtmChangeProductListItemEvent(
            GtmEventType.remove_from_wishlist,
            listedProduct,
            undefined,
            'CZK',
            GtmProductListNameType.wishlist,
            'https://example.com/',
            false,
        );

        expect(result.event).toBe(GtmEventType.remove_from_wishlist);
        expect(result.ecommerce.listName).toBe(GtmProductListNameType.wishlist);
        expect(result.ecommerce.products?.[0]?.listIndex).toBeUndefined();
        expect(result.ecommerce.products?.[0]?.url).toBe('https://example.com/test-product');
    });

    test('should create add to comparison event', () => {
        const result = getGtmChangeProductListItemEvent(
            GtmEventType.add_to_comparison,
            listedProduct,
            1,
            'CZK',
            GtmProductListNameType.product_detail,
            'https://example.com',
            false,
        );

        expect(result.event).toBe(GtmEventType.add_to_comparison);
        expect(result.ecommerce.listName).toBe(GtmProductListNameType.product_detail);
        expect(result.ecommerce.products?.[0]?.quantity).toBe(1);
        expect(result.ecommerce.products?.[0]?.listIndex).toBe(2);
    });

    test('should create remove from comparison event', () => {
        const result = getGtmChangeProductListItemEvent(
            GtmEventType.remove_from_comparison,
            listedProduct,
            undefined,
            'CZK',
            GtmProductListNameType.product_comparison_page,
            'https://example.com',
            false,
        );

        expect(result.event).toBe(GtmEventType.remove_from_comparison);
        expect(result.ecommerce.listName).toBe(GtmProductListNameType.product_comparison_page);
        expect(result.ecommerce.products?.[0]?.listIndex).toBeUndefined();
    });
});
