import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { TypeAvailabilityStatusEnum } from 'graphql/types';
import { GtmEventType } from 'gtm/enums/GtmEventType';
import { getGtmCreateWatchDogEvent } from 'gtm/factories/getGtmCreateWatchDogEvent';
import { WatchdogFormType } from 'types/form';
import { describe, expect, test } from 'vitest';
import { defaultTestDomainConfig } from '../helpers/mockPublicConfig';

const watchdogFormData: WatchdogFormType = {
    email: 'customer@example.com',
    productUuid: 'd6316f87-b06a-45f1-92b4-4a6a347817db',
    gdprAgreement: true,
};

const listedProduct = {
    __typename: 'RegularProduct',
    id: 12345,
    uuid: 'd6316f87-b06a-45f1-92b4-4a6a347817db',
    slug: '/watched-product',
    fullName: 'Watched Product',
    stockQuantity: 0,
    isAllowedNegativeStock: false,
    isSellingDenied: false,
    isCurrentlyOutOfStock: true,
    availableStoresCount: null,
    catalogNumber: 'WATCH-1',
    isMainVariant: false,
    isInquiryType: false,
    unit: {
        __typename: 'Unit',
        name: 'pcs',
    },
    flags: [
        {
            __typename: 'Flag',
            uuid: '18cf6d4a-f243-498b-9b9d-2b989d2fae19',
            name: 'Action',
            rgbColor: '#ff0000',
        },
    ],
    mainImage: {
        __typename: 'Image',
        url: 'https://cdn.example.com/watched-product.jpg',
    },
    price: {
        __typename: 'ProductPrice',
        priceWithVat: '121.00',
        priceWithoutVat: '100.00',
        vatAmount: '21.00',
        isPriceFrom: false,
        percentageDiscount: null,
        basicPrice: {
            __typename: 'Price',
            priceWithVat: '121.00',
        },
    },
    expectedRestockingDate: null,
    availability: {
        __typename: 'Availability',
        name: 'Out of stock',
        status: TypeAvailabilityStatusEnum.OutOfStock,
    },
    brand: {
        __typename: 'Brand',
        name: 'Watch Brand',
    },
    categories: [
        {
            __typename: 'Category',
            name: 'Watches',
        },
    ],
} satisfies TypeListedProductFragment;

describe('getGtmCreateWatchDogEvent', () => {
    test('should create watchdog event with product ecommerce data', () => {
        const event = getGtmCreateWatchDogEvent(watchdogFormData, listedProduct, defaultTestDomainConfig, false, 1);

        expect(event).toEqual({
            event: GtmEventType.create_watchdog,
            eventParameters: {
                email: 'customer@example.com',
                productUuid: 'd6316f87-b06a-45f1-92b4-4a6a347817db',
            },
            ecommerce: {
                currencyCode: 'EUR',
                valueWithoutVat: 100,
                valueWithVat: 121,
                products: [
                    {
                        id: 12345,
                        name: 'Watched Product',
                        availability: 'out_of_stock',
                        flags: ['Action'],
                        priceWithoutVat: 100,
                        priceWithVat: 121,
                        vatAmount: 21,
                        sku: 'WATCH-1',
                        url: 'https://test1.example.com/watched-product',
                        brand: 'Watch Brand',
                        categories: ['Watches'],
                        imageUrl: 'https://cdn.example.com/watched-product.jpg',
                        productType: 'product',
                        quantity: 1,
                        listIndex: 2,
                    },
                ],
                arePricesHidden: false,
            },
            _clear: true,
        });
    });
});
