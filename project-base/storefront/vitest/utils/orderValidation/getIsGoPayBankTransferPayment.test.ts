import { getIsGoPayBankTransferPayment } from 'components/Pages/Order/TransportAndPayment/transportAndPaymentUtils';
import { describe, expect, test, vi } from 'vitest';

vi.mock('next/config', () => ({
    default: () => ({
        serverRuntimeConfig: { internalGraphqlEndpoint: 'https://test.ts/graphql/' },
        publicRuntimeConfig: {
            errorDebuggingLevel: 'no-debug',
            domains: [{ url: 'https://test.ts/' }, { url: 'https://test.ts/' }],
        },
    }),
}));

describe('getIsGoPayBankTransferPayment', () => {
    describe('GoPay bank transfer payment detection', () => {
        test('should return true for GoPay bank transfer payment', () => {
            const payment = {
                uuid: 'payment-uuid-1',
                name: 'GoPay Bank Transfer',
                goPayPaymentMethod: {
                    identifier: 'BANK_ACCOUNT',
                },
            } as any;

            const result = getIsGoPayBankTransferPayment(payment);

            expect(result).toBe(true);
        });

        test('should return false for GoPay payment with different method', () => {
            const payment = {
                uuid: 'payment-uuid-2',
                name: 'GoPay Card Payment',
                goPayPaymentMethod: {
                    identifier: 'CARD',
                },
            } as any;

            const result = getIsGoPayBankTransferPayment(payment);

            expect(result).toBe(false);
        });

        test('should return false for GoPay payment with null method', () => {
            const payment = {
                uuid: 'payment-uuid-3',
                name: 'GoPay Payment',
                goPayPaymentMethod: null,
            } as any;

            const result = getIsGoPayBankTransferPayment(payment);

            expect(result).toBe(false);
        });

        test('should return false for non-GoPay payment', () => {
            const payment = {
                uuid: 'payment-uuid-4',
                name: 'Card Payment',
                goPayPaymentMethod: null,
            } as any;

            const result = getIsGoPayBankTransferPayment(payment);

            expect(result).toBe(false);
        });
    });

    describe('Edge cases', () => {
        test('should return false for null payment', () => {
            const result = getIsGoPayBankTransferPayment(null);

            expect(result).toBe(false);
        });

        test('should return false for undefined payment', () => {
            const result = getIsGoPayBankTransferPayment(undefined as any);

            expect(result).toBe(false);
        });

        test('should return false for payment with undefined goPayPaymentMethod', () => {
            const payment = {
                uuid: 'payment-uuid-5',
                name: 'Some Payment',
                goPayPaymentMethod: undefined,
            } as any;

            const result = getIsGoPayBankTransferPayment(payment);

            expect(result).toBe(false);
        });

        test('should return false for payment with goPayPaymentMethod without identifier', () => {
            const payment = {
                uuid: 'payment-uuid-6',
                name: 'GoPay Payment',
                goPayPaymentMethod: {
                    // identifier missing
                },
            } as any;

            const result = getIsGoPayBankTransferPayment(payment);

            expect(result).toBe(false);
        });

        test('should return false for payment with empty identifier', () => {
            const payment = {
                uuid: 'payment-uuid-7',
                name: 'GoPay Payment',
                goPayPaymentMethod: {
                    identifier: '',
                },
            } as any;

            const result = getIsGoPayBankTransferPayment(payment);

            expect(result).toBe(false);
        });

        test('should handle case sensitivity correctly', () => {
            const payment = {
                uuid: 'payment-uuid-8',
                name: 'GoPay Payment',
                goPayPaymentMethod: {
                    identifier: 'bank_account', // lowercase
                },
            } as any;

            const result = getIsGoPayBankTransferPayment(payment);

            expect(result).toBe(false);
        });

        test('should handle whitespace in identifier', () => {
            const payment = {
                uuid: 'payment-uuid-9',
                name: 'GoPay Payment',
                goPayPaymentMethod: {
                    identifier: ' BANK_ACCOUNT ',
                },
            } as any;

            const result = getIsGoPayBankTransferPayment(payment);

            expect(result).toBe(false);
        });
    });

    describe('Different GoPay payment methods', () => {
        test('should return false for GoPay wallet payment', () => {
            const payment = {
                uuid: 'payment-uuid-10',
                name: 'GoPay Wallet',
                goPayPaymentMethod: {
                    identifier: 'WALLET',
                },
            } as any;

            const result = getIsGoPayBankTransferPayment(payment);

            expect(result).toBe(false);
        });

        test('should return false for GoPay PayPal payment', () => {
            const payment = {
                uuid: 'payment-uuid-11',
                name: 'GoPay PayPal',
                goPayPaymentMethod: {
                    identifier: 'PAYPAL',
                },
            } as any;

            const result = getIsGoPayBankTransferPayment(payment);

            expect(result).toBe(false);
        });

        test('should return false for any other GoPay method', () => {
            const payment = {
                uuid: 'payment-uuid-12',
                name: 'GoPay Other',
                goPayPaymentMethod: {
                    identifier: 'OTHER_METHOD',
                },
            } as any;

            const result = getIsGoPayBankTransferPayment(payment);

            expect(result).toBe(false);
        });
    });
});
