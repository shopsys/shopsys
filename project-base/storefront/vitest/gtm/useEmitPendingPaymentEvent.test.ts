import { renderHook } from '@testing-library/react';
import { useEmitPendingPaymentEvent } from 'gtm/hooks/useEmitPendingPaymentEvent';
import { beforeEach, describe, expect, test, vi } from 'vitest';

const mockGetGtmPaymentEvent = vi.fn();
const mockGtmSafePushEvent = vi.fn();
const mockGetGtmPendingPayment = vi.fn();
const mockRemoveGtmPendingPayment = vi.fn();
const mockBuildPaymentAttemptKey = vi.fn();
const mockCanEmitPaymentEvent = vi.fn();
const mockMarkPaymentEventEmitted = vi.fn();
const mockRemoveGoPayPaymentSession = vi.fn();

vi.mock('gtm/factories/getGtmPaymentEvent', () => ({
    getGtmPaymentEvent: (...args: unknown[]) => mockGetGtmPaymentEvent(...args),
}));

vi.mock('gtm/utils/gtmSafePushEvent', () => ({
    gtmSafePushEvent: (...args: unknown[]) => mockGtmSafePushEvent(...args),
}));

vi.mock('gtm/utils/gtmPaymentEventDedup', () => ({
    buildPaymentAttemptKey: (...args: unknown[]) => mockBuildPaymentAttemptKey(...args),
    canEmitPaymentEvent: (...args: unknown[]) => mockCanEmitPaymentEvent(...args),
    markPaymentEventEmitted: (...args: unknown[]) => mockMarkPaymentEventEmitted(...args),
}));

vi.mock('gtm/utils/gtmPaymentEventLocalStorage', () => ({
    getGtmPendingPaymentFromLocalStorage: () => mockGetGtmPendingPayment(),
    removeGtmPendingPaymentFromLocalStorage: () => mockRemoveGtmPendingPayment(),
}));

vi.mock('utils/goPayPaymentSessionStorage', () => ({
    removeGoPayPaymentSession: () => mockRemoveGoPayPaymentSession(),
}));

describe('useEmitPendingPaymentEvent', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        mockBuildPaymentAttemptKey.mockReturnValue('attempt-key');
        mockCanEmitPaymentEvent.mockReturnValue(true);
        mockGetGtmPaymentEvent.mockReturnValue({ event: 'ec.payment' });
    });

    test('emits ec.payment with isPaymentSuccessful=true for paid state', () => {
        mockGetGtmPendingPayment.mockReturnValue({ orderUuid: 'order-1', paymentTransactionsCount: 1 });

        const { result } = renderHook(() => useEmitPendingPaymentEvent());

        result.current.tryEmitPaymentEvent({
            orderUuid: 'order-1',
            isPaid: true,
            hasPaymentInProcess: false,
            paymentTransactionsCount: 1,
            paymentName: 'GoPay',
            orderNumber: 'ORD-1',
        });

        expect(mockGetGtmPaymentEvent).toHaveBeenCalledWith('ORD-1', 'GoPay', true, 0);
        expect(mockGtmSafePushEvent).toHaveBeenCalledWith({ event: 'ec.payment' });
        expect(mockMarkPaymentEventEmitted).toHaveBeenCalledWith('final', 'attempt-key');
        expect(mockRemoveGtmPendingPayment).toHaveBeenCalled();
        expect(mockRemoveGoPayPaymentSession).toHaveBeenCalled();
    });

    test('emits ec.payment with isPaymentSuccessful=true for InProcess state (optimistic)', () => {
        mockGetGtmPendingPayment.mockReturnValue({ orderUuid: 'order-1', paymentTransactionsCount: 1 });

        const { result } = renderHook(() => useEmitPendingPaymentEvent());

        result.current.tryEmitPaymentEvent({
            orderUuid: 'order-1',
            isPaid: false,
            hasPaymentInProcess: true,
            paymentTransactionsCount: 1,
            paymentName: 'GoPay',
            orderNumber: 'ORD-1',
        });

        expect(mockGetGtmPaymentEvent).toHaveBeenCalledWith('ORD-1', 'GoPay', true, 0);
        expect(mockGtmSafePushEvent).toHaveBeenCalledWith({ event: 'ec.payment' });
        expect(mockMarkPaymentEventEmitted).toHaveBeenCalledWith('final', 'attempt-key');
        expect(mockRemoveGtmPendingPayment).toHaveBeenCalled();
        expect(mockRemoveGoPayPaymentSession).toHaveBeenCalled();
    });

    test('emits ec.payment with isPaymentSuccessful=false for failed state', () => {
        mockGetGtmPendingPayment.mockReturnValue({ orderUuid: 'order-1', paymentTransactionsCount: 1 });

        const { result } = renderHook(() => useEmitPendingPaymentEvent());

        result.current.tryEmitPaymentEvent({
            orderUuid: 'order-1',
            isPaid: false,
            hasPaymentInProcess: false,
            paymentTransactionsCount: 1,
            paymentName: 'GoPay',
            orderNumber: 'ORD-1',
        });

        expect(mockGetGtmPaymentEvent).toHaveBeenCalledWith('ORD-1', 'GoPay', false, 0);
        expect(mockGtmSafePushEvent).toHaveBeenCalledWith({ event: 'ec.payment' });
    });

    test('does not emit when no pending payment exists', () => {
        mockGetGtmPendingPayment.mockReturnValue(null);

        const { result } = renderHook(() => useEmitPendingPaymentEvent());

        result.current.tryEmitPaymentEvent({
            orderUuid: 'order-1',
            isPaid: true,
            hasPaymentInProcess: false,
            paymentTransactionsCount: 1,
            paymentName: 'GoPay',
            orderNumber: 'ORD-1',
        });

        expect(mockGtmSafePushEvent).not.toHaveBeenCalled();
    });

    test('does not emit when pending payment orderUuid does not match', () => {
        mockGetGtmPendingPayment.mockReturnValue({ orderUuid: 'different-order' });

        const { result } = renderHook(() => useEmitPendingPaymentEvent());

        result.current.tryEmitPaymentEvent({
            orderUuid: 'order-1',
            isPaid: true,
            hasPaymentInProcess: false,
            paymentTransactionsCount: 1,
            paymentName: 'GoPay',
            orderNumber: 'ORD-1',
        });

        expect(mockGtmSafePushEvent).not.toHaveBeenCalled();
    });

    test('does not emit twice (idempotent)', () => {
        mockGetGtmPendingPayment.mockReturnValue({ orderUuid: 'order-1', paymentTransactionsCount: 1 });

        const { result } = renderHook(() => useEmitPendingPaymentEvent());

        result.current.tryEmitPaymentEvent({
            orderUuid: 'order-1',
            isPaid: true,
            hasPaymentInProcess: false,
            paymentTransactionsCount: 1,
            paymentName: 'GoPay',
            orderNumber: 'ORD-1',
        });

        result.current.tryEmitPaymentEvent({
            orderUuid: 'order-1',
            isPaid: true,
            hasPaymentInProcess: false,
            paymentTransactionsCount: 1,
            paymentName: 'GoPay',
            orderNumber: 'ORD-1',
        });

        expect(mockGtmSafePushEvent).toHaveBeenCalledTimes(1);
    });

    test('cleans up without emitting when dedup blocks emission', () => {
        mockGetGtmPendingPayment.mockReturnValue({ orderUuid: 'order-1', paymentTransactionsCount: 1 });
        mockCanEmitPaymentEvent.mockReturnValue(false);

        const { result } = renderHook(() => useEmitPendingPaymentEvent());

        result.current.tryEmitPaymentEvent({
            orderUuid: 'order-1',
            isPaid: true,
            hasPaymentInProcess: false,
            paymentTransactionsCount: 1,
            paymentName: 'GoPay',
            orderNumber: 'ORD-1',
        });

        expect(mockGtmSafePushEvent).not.toHaveBeenCalled();
        expect(mockRemoveGtmPendingPayment).toHaveBeenCalled();
        expect(mockRemoveGoPayPaymentSession).toHaveBeenCalled();
    });
});
