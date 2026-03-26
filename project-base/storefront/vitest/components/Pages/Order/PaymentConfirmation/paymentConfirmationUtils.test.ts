import { act, renderHook, waitFor } from '@testing-library/react';
import {
    getPaymentSessionExpiredErrorMessage,
    resolvePaymentPageStatus,
    useUpdatePaymentStatus,
} from 'components/Pages/Order/PaymentConfirmation/paymentConfirmationUtils';
import { TypePaymentContentPageStatusEnum } from 'graphql/types';
import { beforeEach, describe, expect, test, vi } from 'vitest';

const mockUpdatePaymentStatusMutation = vi.fn();
const mockTryEmitPaymentEvent = vi.fn();
const mockGetGtmCreateOrderEventFromLocalStorage = vi.fn();
const mockRemoveGtmCreateOrderEventFromLocalStorage = vi.fn();
const mockGetUserFriendlyErrors = vi.fn();

vi.mock('graphql/requests/orders/mutations/UpdatePaymentStatusMutation.generated', () => ({
    useUpdatePaymentStatusMutation: () => [{}, (...args: unknown[]) => mockUpdatePaymentStatusMutation(...args)],
}));

vi.mock('gtm/hooks/useEmitPendingPaymentEvent', () => ({
    useEmitPendingPaymentEvent: () => ({
        tryEmitPaymentEvent: (...args: unknown[]) => mockTryEmitPaymentEvent(...args),
    }),
}));

vi.mock('gtm/utils/gtmCreateOrderEventLocalStorage', () => ({
    getGtmCreateOrderEventFromLocalStorage: () => mockGetGtmCreateOrderEventFromLocalStorage(),
    removeGtmCreateOrderEventFromLocalStorage: () => mockRemoveGtmCreateOrderEventFromLocalStorage(),
}));

vi.mock('utils/errors/friendlyErrorMessageParser', () => ({
    getUserFriendlyErrors: (...args: unknown[]) => mockGetUserFriendlyErrors(...args),
}));

const createPaymentStatus = (overrides = {}) => ({
    isPaid: false,
    hasPaymentInProcess: false,
    number: 'ORD-123',
    paymentTransactionsCount: 1,
    urlHash: 'hash-123',
    items: [{ type: 'payment', payment: { name: 'GoPay', type: 'GOPAY' } }],
    ...overrides,
});

const createUpdatePaymentStatusResult = (paymentStatusOverrides = {}, paymentPageContentOverrides = {}) => ({
    ...createPaymentStatus(paymentStatusOverrides),
    paymentPageContent: {
        status: TypePaymentContentPageStatusEnum.Failed,
        content: 'payment content',
        ...paymentPageContentOverrides,
    },
});

describe('resolvePaymentPageStatus', () => {
    test('returns Successful when isPaid is true', () => {
        expect(resolvePaymentPageStatus(createPaymentStatus({ isPaid: true }) as any)).toBe(
            TypePaymentContentPageStatusEnum.Successful,
        );
    });

    test('returns InProcess when hasPaymentInProcess is true', () => {
        expect(resolvePaymentPageStatus(createPaymentStatus({ hasPaymentInProcess: true }) as any)).toBe(
            TypePaymentContentPageStatusEnum.InProcess,
        );
    });

    test('returns Failed when neither paid nor in process', () => {
        expect(resolvePaymentPageStatus(createPaymentStatus() as any)).toBe(TypePaymentContentPageStatusEnum.Failed);
    });

    test('returns Failed for undefined input', () => {
        expect(resolvePaymentPageStatus(undefined)).toBe(TypePaymentContentPageStatusEnum.Failed);
    });
});

describe('getPaymentSessionExpiredErrorMessage', () => {
    const mockT = (key: string) => key;

    test('returns empty string when no errors', () => {
        expect(getPaymentSessionExpiredErrorMessage(mockT as any, undefined)).toBe('');
    });

    test('returns error message for expired page', () => {
        mockGetUserFriendlyErrors.mockReturnValue({
            applicationError: { type: 'order-sent-page-not-available' },
        });

        const error = { graphQLErrors: [{ message: 'test' }] } as any;

        expect(getPaymentSessionExpiredErrorMessage(mockT as any, error)).toBe('Order sent page is not available.');
    });
});

describe('useUpdatePaymentStatus', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        mockGetGtmCreateOrderEventFromLocalStorage.mockReturnValue({});
    });

    test('fetches payment status on mount', async () => {
        const paymentStatus = createUpdatePaymentStatusResult(
            { isPaid: true },
            { status: TypePaymentContentPageStatusEnum.Successful },
        );
        mockUpdatePaymentStatusMutation.mockResolvedValue({
            data: { UpdatePaymentStatus: paymentStatus },
        });

        const { result } = renderHook(() => useUpdatePaymentStatus('order-uuid', 'hash-123'));

        await waitFor(() => {
            expect(result.current.paymentStatusData).toBeDefined();
        });

        expect(mockUpdatePaymentStatusMutation).toHaveBeenCalledWith({
            orderUuid: 'order-uuid',
            orderPaymentStatusPageValidityHash: 'hash-123',
        });
    });

    test('emits ec.payment for terminal state (paid)', async () => {
        const paymentStatus = createUpdatePaymentStatusResult(
            { isPaid: true },
            { status: TypePaymentContentPageStatusEnum.Successful },
        );
        mockUpdatePaymentStatusMutation.mockResolvedValue({
            data: { UpdatePaymentStatus: paymentStatus },
        });

        renderHook(() => useUpdatePaymentStatus('order-uuid', 'hash-123'));

        await waitFor(() => {
            expect(mockTryEmitPaymentEvent).toHaveBeenCalledWith({
                orderUuid: 'order-uuid',
                isPaid: true,
                hasPaymentInProcess: false,
                paymentTransactionsCount: 1,
                paymentName: 'GoPay',
                orderNumber: 'ORD-123',
            });
        });
    });

    test('emits ec.payment for terminal state (failed)', async () => {
        const paymentStatus = createUpdatePaymentStatusResult({ isPaid: false, hasPaymentInProcess: false });
        mockUpdatePaymentStatusMutation.mockResolvedValue({
            data: { UpdatePaymentStatus: paymentStatus },
        });

        renderHook(() => useUpdatePaymentStatus('order-uuid', 'hash-123'));

        await waitFor(() => {
            expect(mockTryEmitPaymentEvent).toHaveBeenCalledWith(
                expect.objectContaining({ isPaid: false, hasPaymentInProcess: false }),
            );
        });
    });

    test('retries once when InProcess then stops — verifies final state and call count', async () => {
        const inProcessStatus = createUpdatePaymentStatusResult(
            { hasPaymentInProcess: true },
            { status: TypePaymentContentPageStatusEnum.InProcess },
        );
        const failedStatus = createUpdatePaymentStatusResult({ hasPaymentInProcess: false, isPaid: false });

        mockUpdatePaymentStatusMutation
            .mockResolvedValueOnce({ data: { UpdatePaymentStatus: inProcessStatus } })
            .mockResolvedValueOnce({ data: { UpdatePaymentStatus: failedStatus } });

        renderHook(() => useUpdatePaymentStatus('order-uuid', 'hash'));

        // Wait for both calls to complete (initial + 1 auto-retry)
        await waitFor(
            () => {
                expect(mockUpdatePaymentStatusMutation).toHaveBeenCalledTimes(2);
            },
            { timeout: 5000 },
        );

        // Should not retry more than once
        expect(mockUpdatePaymentStatusMutation).toHaveBeenCalledTimes(2);
    });

    test('returns resolved status from recheckPaymentStatus for manual status check', async () => {
        const paidStatus = createUpdatePaymentStatusResult(
            { isPaid: true },
            { status: TypePaymentContentPageStatusEnum.Successful },
        );

        mockUpdatePaymentStatusMutation.mockResolvedValue({ data: { UpdatePaymentStatus: paidStatus } });

        const { result } = renderHook(() => useUpdatePaymentStatus('order-uuid', 'hash'));

        await waitFor(() => {
            expect(result.current.paymentStatusData).toBeDefined();
        });

        // Clear mock and set up new response for manual recheck
        mockUpdatePaymentStatusMutation.mockClear();
        const updatedStatus = createUpdatePaymentStatusResult(
            { isPaid: true, paymentTransactionsCount: 2 },
            { status: TypePaymentContentPageStatusEnum.Successful },
        );
        mockUpdatePaymentStatusMutation.mockResolvedValue({ data: { UpdatePaymentStatus: updatedStatus } });

        await act(async () => {
            await expect(result.current.recheckPaymentStatus()).resolves.toBe(
                TypePaymentContentPageStatusEnum.Successful,
            );
        });

        await waitFor(() => {
            expect(mockUpdatePaymentStatusMutation).toHaveBeenCalledTimes(1);
        });
    });

    test('sets statusError when mutation returns no data', async () => {
        mockUpdatePaymentStatusMutation.mockResolvedValue({ data: undefined });

        const { result } = renderHook(() => useUpdatePaymentStatus('order-uuid', 'hash'));

        await waitFor(() => {
            expect(result.current.statusError).toBe(true);
        });

        expect(result.current.paymentStatusData).toBeUndefined();
    });

    test('returns error from recheckPaymentStatus when mutation returns no data', async () => {
        const paidStatus = createUpdatePaymentStatusResult(
            { isPaid: true },
            { status: TypePaymentContentPageStatusEnum.Successful },
        );
        mockUpdatePaymentStatusMutation.mockResolvedValueOnce({ data: { UpdatePaymentStatus: paidStatus } });

        const { result } = renderHook(() => useUpdatePaymentStatus('order-uuid', 'hash'));

        await waitFor(() => {
            expect(result.current.paymentStatusData).toBeDefined();
        });

        mockUpdatePaymentStatusMutation.mockClear();
        mockUpdatePaymentStatusMutation.mockResolvedValue({ data: undefined });

        await act(async () => {
            await expect(result.current.recheckPaymentStatus()).resolves.toBe('error');
        });
    });

    test('does not call mutation when orderUuid is empty', async () => {
        const { result } = renderHook(() => useUpdatePaymentStatus('', 'hash'));

        await waitFor(() => {
            expect(result.current.statusError).toBe(true);
        });

        expect(mockUpdatePaymentStatusMutation).not.toHaveBeenCalled();
    });
});
