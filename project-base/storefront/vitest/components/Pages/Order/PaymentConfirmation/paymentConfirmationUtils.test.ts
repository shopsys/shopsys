import { renderHook, waitFor } from '@testing-library/react';
import { useUpdatePaymentStatus } from 'components/Pages/Order/PaymentConfirmation/paymentConfirmationUtils';
import { CombinedError } from 'urql';
import { beforeEach, describe, expect, test, vi } from 'vitest';

const {
    getGtmCreateOrderEventFromLocalStorageMock,
    paymentStatusMutationStateMock,
    removeGtmCreateOrderEventFromLocalStorageMock,
    updatePaymentStatusMutationMock,
} = vi.hoisted(() => ({
    getGtmCreateOrderEventFromLocalStorageMock: vi.fn(),
    paymentStatusMutationStateMock: {
        data: undefined,
        error: undefined as CombinedError | undefined,
        fetching: false,
    },
    removeGtmCreateOrderEventFromLocalStorageMock: vi.fn(),
    updatePaymentStatusMutationMock: vi.fn(),
}));

vi.mock('graphql/requests/orders/mutations/UpdatePaymentStatusMutation.generated', () => ({
    useUpdatePaymentStatusMutation: () => [paymentStatusMutationStateMock, updatePaymentStatusMutationMock],
}));

vi.mock('gtm/utils/gtmCreateOrderEventLocalStorage', () => ({
    getGtmCreateOrderEventFromLocalStorage: getGtmCreateOrderEventFromLocalStorageMock,
    removeGtmCreateOrderEventFromLocalStorage: removeGtmCreateOrderEventFromLocalStorageMock,
}));

vi.mock('gtm/utils/gtmSafePushEvent', () => ({
    gtmSafePushEvent: vi.fn(),
}));

describe('useUpdatePaymentStatus', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        paymentStatusMutationStateMock.data = undefined;
        paymentStatusMutationStateMock.error = undefined;
        paymentStatusMutationStateMock.fetching = false;
        getGtmCreateOrderEventFromLocalStorageMock.mockReturnValue({});
    });

    test('exposes generated mutation error without keeping payment status update pending', async () => {
        const paymentStatusUpdateError = new CombinedError({
            graphQLErrors: [{ message: 'GraphQL Internal Server Error' }],
        });
        paymentStatusMutationStateMock.error = paymentStatusUpdateError;
        updatePaymentStatusMutationMock.mockResolvedValueOnce({
            data: undefined,
            error: paymentStatusUpdateError,
        });

        const { result } = renderHook(() => useUpdatePaymentStatus('order-uuid', true, 'return-hash'));

        await waitFor(() => expect(updatePaymentStatusMutationMock).toHaveBeenCalledWith({ orderUuid: 'order-uuid' }));
        expect(result.current.error).toBe(paymentStatusUpdateError);
        expect(result.current.fetching).toBe(false);
        expect(result.current.data).toBeUndefined();
        expect(removeGtmCreateOrderEventFromLocalStorageMock).not.toHaveBeenCalled();
    });
});
