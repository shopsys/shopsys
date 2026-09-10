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
        priceWithoutVat: '5.00',
        priceWithVat: '5.00',
    },
    transport: {
        name: 'Packetery',
        price: {
            priceWithoutVat: '4.00',
            priceWithVat: '4.84',
        },
    },
    giftVouchers: [],
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
            currency: 'EUR',
            id: '202600001',
            value: 100,
            valueWithTax: 121,
            valueTax: 21,
            paymentPriceWithoutVat: 10,
            paymentPriceWithVat: 12.1,
            transportPriceWithoutVat: 4,
            transportPriceWithVat: 4.84,
            transportType: 'Packetery',
            discountAmount: 5,
            discountAmountWithTax: 5,
            promoCodes: ['PROMO'],
            coupons: ['PROMO'],
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
                    priceWithoutVat: '***',
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

        expect(result.value).toBeNull();
        expect(result.valueWithTax).toBeNull();
        expect(result.paymentPriceWithoutVat).toBeNull();
        expect(result.paymentPriceWithVat).toBeNull();
        expect(result.transportPriceWithoutVat).toBeNull();
        expect(result.transportPriceWithVat).toBeNull();
        expect(result.discountAmount).toBeNull();
        expect(result.discountAmountWithTax).toBeNull();
        expect(result.reviewConsents).toEqual({
            google: false,
            seznam: false,
            heureka: false,
        });
    });

    test('should omit review consents when they are not available on the current domain', () => {
        const result = getGtmCreateOrderEventOrderPart(cart, payment, [], '202600001', undefined, domainConfig);

        expect(result).not.toHaveProperty('reviewConsents');
    });

    test('should report zero voucher values and no voucher names without applied vouchers', () => {
        const result = getGtmCreateOrderEventOrderPart(cart, payment, [], '202600001', undefined, domainConfig);

        expect(result.voucherAmount).toBe(0);
        expect(result.voucherAmountWithTax).toBe(0);
        expect(result.voucherName).toEqual([]);
    });

    test('should aggregate gift voucher amount, tax and names', () => {
        const result = getGtmCreateOrderEventOrderPart(
            {
                ...cart,
                giftVouchers: [
                    {
                        code: 'VOUCHER-1',
                        valueWithVat: '36.30',
                        valueWithoutVat: '30.00',
                        validUntil: '2030-01-01T00:00:00+00:00',
                        productName: 'Electronic gift voucher 1000 CZK',
                    },
                    {
                        code: 'VOUCHER-2',
                        valueWithVat: '20.50',
                        valueWithoutVat: '20.50',
                        validUntil: '2030-01-01T00:00:00+00:00',
                        productName: null,
                    },
                ],
            },
            payment,
            [],
            '202600001',
            undefined,
            domainConfig,
        );

        expect(result.voucherAmount).toBe(50.5);
        expect(result.voucherAmountWithTax).toBe(56.8);
        expect(result.voucherName).toEqual(['Electronic gift voucher 1000 CZK', 'VOUCHER-2']);
    });
});
