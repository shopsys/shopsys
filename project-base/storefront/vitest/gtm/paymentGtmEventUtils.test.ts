import { TypeOrderConfirmationPageContentStatusEnum } from 'graphql/types';
import {
    getGtmPaymentEventFromPaymentStatusUpdate,
    getGtmPaymentRetryCount,
    getIsPaymentSuccessfulByConfirmationStatus,
} from 'gtm/utils/paymentGtmEventUtils';
import { describe, expect, test } from 'vitest';

describe('paymentGtmEventUtils', () => {
    test('maps successful and in-process payment states as successful GTM payment', () => {
        expect(getIsPaymentSuccessfulByConfirmationStatus(TypeOrderConfirmationPageContentStatusEnum.Successful)).toBe(
            true,
        );
        expect(getIsPaymentSuccessfulByConfirmationStatus(TypeOrderConfirmationPageContentStatusEnum.InProcess)).toBe(
            true,
        );
    });

    test('maps failed payment state as unsuccessful GTM payment', () => {
        expect(getIsPaymentSuccessfulByConfirmationStatus(TypeOrderConfirmationPageContentStatusEnum.Failed)).toBe(
            false,
        );
    });

    test('uses zero retry count for the first payment transaction', () => {
        expect(getGtmPaymentRetryCount(0)).toBe(0);
        expect(getGtmPaymentRetryCount(1)).toBe(0);
    });

    test('uses previous payment transactions as retry count', () => {
        expect(getGtmPaymentRetryCount(2)).toBe(1);
        expect(getGtmPaymentRetryCount(3)).toBe(2);
    });

    test('creates GTM payment event from payment status update', () => {
        expect(
            getGtmPaymentEventFromPaymentStatusUpdate({
                orderNumber: '123',
                paymentName: 'GoPay',
                lastPaymentStatus: 'CANCELED',
                paymentTransactionsCount: 2,
                confirmationPageContent: {
                    status: TypeOrderConfirmationPageContentStatusEnum.Failed,
                },
            }),
        ).toEqual({
            event: 'ec.payment',
            ecommerce: {
                id: '123',
                isPaymentSuccessful: false,
                paymentRetryCount: 1,
                paymentStatus: 'CANCELED',
                PaymentFalseReason: undefined,
                paymentType: 'GoPay',
            },
            _clear: true,
        });
    });
});
