import { renderHook } from '@testing-library/react';
import {
    getIsWaitingForPaymentStatusUpdate,
    getOrderConfirmationPaymentView,
    useOrderConfirmationOrder,
} from 'components/Pages/OrderConfirmation/orderConfirmationPageUtils';
import { TypeOrderConfirmationPageContentStatusEnum } from 'graphql/types';
import { describe, expect, test, vi } from 'vitest';

const { orderQueryMock, paymentStatusMock } = vi.hoisted(() => ({
    orderQueryMock: vi.fn(),
    paymentStatusMock: vi.fn(),
}));

vi.mock('graphql/requests/orders/queries/OrderDetailByHashQuery.generated', () => ({
    useOrderDetailByHashQuery: orderQueryMock,
}));

vi.mock('components/Pages/Order/PaymentConfirmation/paymentConfirmationUtils', () => ({
    useUpdatePaymentStatus: paymentStatusMock,
}));

describe('useOrderConfirmationOrder', () => {
    test('uses current payment instructions after switching from failed GoPay to bank transfer', () => {
        const failedPayment = {
            confirmationPageContent: {
                status: TypeOrderConfirmationPageContentStatusEnum.Failed,
                content: 'GoPay payment failed',
            },
            isAwaitingPayment: true,
            isPaid: false,
            hasPaymentInProcess: false,
        };
        const goPayOrder = {
            ...failedPayment,
            uuid: 'order-uuid',
            hasExternalPayment: true,
        };
        const bankTransferOrder = {
            ...goPayOrder,
            hasExternalPayment: false,
            isAwaitingPayment: false,
            confirmationPageContent: {
                status: TypeOrderConfirmationPageContentStatusEnum.Successful,
                content: 'Bank account details and QR code',
            },
        };
        const context = {
            type: 'ready' as const,
            orderUrlHash: 'order-hash',
            shouldUpdatePaymentStatus: true,
            paymentStatusUpdateTrigger: 'return-hash',
        };
        orderQueryMock.mockReturnValue([{ data: { order: goPayOrder }, fetching: false }]);
        paymentStatusMock.mockReturnValue({ data: { UpdatePaymentStatus: failedPayment } });
        const { result, rerender } = renderHook(() => useOrderConfirmationOrder(context, 'order-hash', ''));
        expect(result.current.order?.confirmationPageContent).toEqual(failedPayment.confirmationPageContent);

        orderQueryMock.mockReturnValue([{ data: { order: bankTransferOrder }, fetching: false }]);
        rerender();

        expect(result.current.order).toEqual(bankTransferOrder);
        expect(result.current.isWaitingForPaymentStatusUpdate).toBe(false);
        expect(result.current.hasPaymentStatusUpdateError).toBe(false);
        expect(getOrderConfirmationPaymentView(result.current.order!, context)).toMatchObject({
            isPaymentFailed: false,
            isPaymentSuccessful: true,
            shouldShowPaymentGateway: false,
        });
    });
});

describe('getIsWaitingForPaymentStatusUpdate', () => {
    test('waits while requested payment status update has neither data nor error', () => {
        expect(getIsWaitingForPaymentStatusUpdate(true, false, false)).toBe(true);
    });

    test('stops waiting after payment status update resolves with data or error', () => {
        expect(getIsWaitingForPaymentStatusUpdate(true, false, true)).toBe(false);
        expect(getIsWaitingForPaymentStatusUpdate(true, true, false)).toBe(false);
        expect(getIsWaitingForPaymentStatusUpdate(false, false, false)).toBe(false);
    });
});
