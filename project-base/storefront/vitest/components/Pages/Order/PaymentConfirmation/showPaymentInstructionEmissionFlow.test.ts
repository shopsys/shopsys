import { act, renderHook, waitFor } from '@testing-library/react';
import { useUpdatePaymentStatus } from 'components/Pages/Order/PaymentConfirmation/paymentConfirmationUtils';
import { TypeOrderItemTypeEnum, TypePaymentContentPageStatusEnum } from 'graphql/types';
import { saveGtmPendingPaymentInLocalStorage } from 'gtm/utils/gtmPaymentEventLocalStorage';
import { beforeEach, describe, expect, test, vi } from 'vitest';

const mockUpdatePaymentStatusMutation = vi.fn();

vi.mock('graphql/requests/orders/mutations/UpdatePaymentStatusMutation.generated', () => ({
    useUpdatePaymentStatusMutation: () => [{}, (...args: unknown[]) => mockUpdatePaymentStatusMutation(...args)],
}));

vi.mock('utils/errors/friendlyErrorMessageParser', () => ({
    getUserFriendlyErrors: vi.fn(),
}));

vi.mock('gtm/utils/gtmCreateOrderEventLocalStorage', () => ({
    getGtmCreateOrderEventFromLocalStorage: () => ({}),
    removeGtmCreateOrderEventFromLocalStorage: vi.fn(),
}));

const buildPaymentStatusResult = (overrides: Record<string, unknown> = {}) => ({
    isPaid: false,
    hasPaymentInProcess: false,
    number: 'ORD-1',
    paymentTransactionsCount: 1,
    urlHash: 'order-hash',
    items: [{ type: TypeOrderItemTypeEnum.Payment, payment: { name: 'GoPay', type: 'GOPAY' } }],
    paymentPageContent: {
        status: TypePaymentContentPageStatusEnum.Successful,
        content: 'content',
    },
    ...overrides,
});

describe('InProcess optimistic ec.payment emission', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        localStorage.clear();
        sessionStorage.clear();
        (window as any).dataLayer = [];
    });

    test('emits ec.payment optimistically on InProcess mount and does not re-emit on second mount (pending payment cleared)', async () => {
        // Original PayOrder saved pendingPayment with paymentTransactionsCount=1 (count after the attempt).
        saveGtmPendingPaymentInLocalStorage({
            orderUuid: 'order-uuid',
            orderNumber: 'ORD-1',
            paymentName: 'GoPay',
            paymentTransactionsCount: 1,
            domainUrl: 'https://test.example.com',
        });

        // First mount: user back-navigated from bank gateway, payment is still InProcess.
        mockUpdatePaymentStatusMutation.mockResolvedValue({
            data: {
                UpdatePaymentStatus: buildPaymentStatusResult({
                    hasPaymentInProcess: true,
                    paymentPageContent: {
                        status: TypePaymentContentPageStatusEnum.InProcess,
                        content: 'in process',
                    },
                }),
            },
        });

        const firstRender = renderHook(() => useUpdatePaymentStatus('order-uuid', 'hash-1'));

        await waitFor(() => {
            expect(firstRender.result.current.paymentStatusData).toBeDefined();
        });

        // ec.payment is emitted immediately on InProcess with isPaymentSuccessful=true (optimistic).
        await waitFor(() => {
            expect((window as any).dataLayer).toHaveLength(1);
        });

        expect((window as any).dataLayer[0]).toMatchObject({
            event: 'ec.payment',
            ecommerce: expect.objectContaining({
                isPaymentSuccessful: true,
                paymentType: 'GoPay',
                id: 'ORD-1',
            }),
        });

        // Pending payment is cleared after emission.
        expect(localStorage.getItem('gtmPendingPayment')).toBeNull();

        // Let the 10ms auto-retry fetch settle. It calls the hook again, but the in-memory
        // hasFiredRef (same hook instance) short-circuits before the localStorage check.
        await act(async () => {
            await new Promise((resolve) => setTimeout(resolve, 50));
        });

        expect((window as any).dataLayer).toHaveLength(1);

        firstRender.unmount();

        // Second mount: user returns to /order-payment-confirmation with Successful state.
        // Different hook instance, so hasFiredRef starts fresh — cleared pendingPayment is what
        // prevents re-emission here.
        mockUpdatePaymentStatusMutation.mockResolvedValue({
            data: {
                UpdatePaymentStatus: buildPaymentStatusResult({
                    isPaid: true,
                    hasPaymentInProcess: false,
                }),
            },
        });

        const secondRender = renderHook(() => useUpdatePaymentStatus('order-uuid', 'hash-1'));

        await waitFor(() => {
            expect(secondRender.result.current.paymentStatusData).toBeDefined();
        });

        // No second emission — pending payment was cleared on first mount.
        expect((window as any).dataLayer).toHaveLength(1);
    });

    test('emits once on InProcess mount; manual recheck returning Successful does not re-emit (same component instance)', async () => {
        saveGtmPendingPaymentInLocalStorage({
            orderUuid: 'order-uuid',
            orderNumber: 'ORD-1',
            paymentName: 'GoPay',
            paymentTransactionsCount: 1,
            domainUrl: 'https://test.example.com',
        });

        // Initial fetch: InProcess → emits ec.payment(isPaymentSuccessful=true).
        mockUpdatePaymentStatusMutation.mockResolvedValue({
            data: {
                UpdatePaymentStatus: buildPaymentStatusResult({
                    hasPaymentInProcess: true,
                    paymentPageContent: {
                        status: TypePaymentContentPageStatusEnum.InProcess,
                        content: 'in process',
                    },
                }),
            },
        });

        const { result } = renderHook(() => useUpdatePaymentStatus('order-uuid', 'hash-1'));

        await waitFor(() => {
            expect(result.current.paymentStatusData).toBeDefined();
        });

        await waitFor(() => {
            expect((window as any).dataLayer).toHaveLength(1);
        });

        // Let the 10ms auto-retry settle.
        await act(async () => {
            await new Promise((resolve) => setTimeout(resolve, 50));
        });

        // Switch mock to Successful and manually recheck — hasFiredRef blocks re-emission.
        mockUpdatePaymentStatusMutation.mockResolvedValue({
            data: {
                UpdatePaymentStatus: buildPaymentStatusResult({
                    isPaid: true,
                    hasPaymentInProcess: false,
                }),
            },
        });

        await act(async () => {
            await result.current.recheckPaymentStatus();
        });

        expect((window as any).dataLayer).toHaveLength(1);
        expect((window as any).dataLayer[0]).toMatchObject({
            event: 'ec.payment',
            ecommerce: expect.objectContaining({
                isPaymentSuccessful: true,
            }),
        });
    });
});
