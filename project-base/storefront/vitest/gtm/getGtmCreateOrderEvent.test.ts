import { getGtmCreateOrderEventOrderPart } from 'gtm/factories/getGtmCreateOrderEvent';
import { getGtmReviewConsents } from 'gtm/utils/getGtmReviewConsents';
import { describe, expect, test } from 'vitest';

const domainConfig = {
    currencyCode: 'EUR',
    url: 'https://example.com',
} as any;

const payment = {
    name: 'Credit card',
    price: {
        priceWithoutVat: '10.00',
        priceWithVat: '12.10',
    },
} as any;

const cart = {
    totalPrice: {
        priceWithoutVat: '100.00',
        priceWithVat: '121.00',
        vatAmount: '21.00',
    },
    totalDiscountPrice: {
        priceWithVat: '5.00',
    },
    transport: {
        name: 'Packetery',
        price: {
            priceWithoutVat: '4.00',
            priceWithVat: '4.84',
        },
    },
    items: [],
} as any;

describe('getGtmCreateOrderEventOrderPart', () => {
    test('should include payment, transport and discount order values', () => {
        const result = getGtmCreateOrderEventOrderPart(
            cart,
            payment,
            [{ code: 'PROMO' }] as any,
            '202600001',
            getGtmReviewConsents(true),
            domainConfig,
        );

        expect(result).toMatchObject({
            currencyCode: 'EUR',
            id: '202600001',
            valueWithoutVat: 100,
            valueWithVat: 121,
            vatAmount: 21,
            paymentPriceWithoutVat: 10,
            paymentPriceWithVat: 12.1,
            transportPriceWithoutVat: 4,
            transportPriceWithVat: 4.84,
            transportType: 'Packetery',
            discountAmount: 5,
            promoCodes: ['PROMO'],
            paymentType: 'Credit card',
            reviewConsents: {
                google: true,
                seznam: true,
                heureka: true,
            },
        });
    });

    test('should hide price-related values when prices are hidden', () => {
        const result = getGtmCreateOrderEventOrderPart(
            {
                ...cart,
                totalPrice: {
                    priceWithoutVat: '***',
                    priceWithVat: '***',
                    vatAmount: '0',
                },
                totalDiscountPrice: {
                    priceWithVat: '***',
                },
                transport: {
                    ...cart.transport,
                    price: {
                        priceWithoutVat: '***',
                        priceWithVat: '***',
                    },
                },
            },
            {
                ...payment,
                price: {
                    priceWithoutVat: '***',
                    priceWithVat: '***',
                },
            },
            [],
            '202600001',
            getGtmReviewConsents(false),
            domainConfig,
        );

        expect(result.valueWithoutVat).toBeNull();
        expect(result.valueWithVat).toBeNull();
        expect(result.paymentPriceWithoutVat).toBeNull();
        expect(result.paymentPriceWithVat).toBeNull();
        expect(result.transportPriceWithoutVat).toBeNull();
        expect(result.transportPriceWithVat).toBeNull();
        expect(result.discountAmount).toBeNull();
        expect(result.reviewConsents).toEqual({
            google: false,
            seznam: false,
            heureka: false,
        });
    });
});
