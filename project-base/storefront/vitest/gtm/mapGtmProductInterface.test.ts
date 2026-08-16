import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { TypeAvailabilityStatusEnum } from 'graphql/types';
import { mapGtmProductInterface } from 'gtm/mappers/mapGtmProductInterface';
import { describe, expect, test, vi } from 'vitest';

vi.mock('utils/domain/domainConfig', () => ({
    getFallbackTimezoneByDomainUrl: () => 'Europe/Prague',
}));

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
    flags: [],
    mainImage: null,
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
    expectedRestockingDate: null,
    availability: { __typename: 'Availability', name: 'In stock', status: TypeAvailabilityStatusEnum.InStock },
    availableStoresCount: 1,
    catalogNumber: 'TEST-42',
    brand: null,
    categories: [],
    isMainVariant: false,
    isInquiryType: false,
    reviewsSummary: null,
    unit: { __typename: 'Unit', name: 'pcs' },
} satisfies TypeListedProductFragment;

describe('mapGtmProductInterface', () => {
    test('should map the in stock availability status to the in_stock slug without the availability date', () => {
        const result = mapGtmProductInterface(
            // an in-stock product exposes a valid restocking date as well, but the data layer must not send it
            { ...listedProduct, expectedRestockingDate: '2026-07-30T22:00:00+00:00' },
            'https://example.com',
        );

        expect(result.availability).toBe('in_stock');
        expect(result).not.toHaveProperty('availability_date');
    });

    test('should map the out of stock availability status to the out_of_stock slug without the availability date', () => {
        const result = mapGtmProductInterface(
            {
                ...listedProduct,
                availability: {
                    __typename: 'Availability',
                    name: 'Out of stock',
                    status: TypeAvailabilityStatusEnum.OutOfStock,
                },
            },
            'https://example.com',
        );

        expect(result.availability).toBe('out_of_stock');
        expect(result).not.toHaveProperty('availability_date');
    });

    test('should omit the availability date when the restocking date is not a valid ISO 8601 date-time', () => {
        const result = mapGtmProductInterface(
            {
                ...listedProduct,
                availability: {
                    __typename: 'Availability',
                    name: 'Expecting 7/31/2026',
                    status: TypeAvailabilityStatusEnum.ExpectedRestock,
                },
                expectedRestockingDate: 'not-a-date',
            },
            'https://example.com',
        );

        expect(result.availability).toBe('expected_restock');
        expect(result).not.toHaveProperty('availability_date');
    });

    test('should map the expected restock availability status to the expected_restock slug with the availability date', () => {
        const result = mapGtmProductInterface(
            {
                ...listedProduct,
                availability: {
                    __typename: 'Availability',
                    name: 'Expecting 7/31/2026',
                    status: TypeAvailabilityStatusEnum.ExpectedRestock,
                },
                expectedRestockingDate: '2026-07-30T22:00:00+00:00',
            },
            'https://example.com',
        );

        expect(result.availability).toBe('expected_restock');
        expect(result.availability_date).toBe('2026-07-31');
    });
});
