import { useUpdatePaymentStatusMutation } from 'graphql/requests/orders/mutations/UpdatePaymentStatusMutation.generated';
import { useEffect, useEffectEvent, useRef } from 'react';
import { hasOrderExternalPaymentContext } from 'utils/mappers/order';

type OrderWithPaymentStatus = {
    uuid: string;
    hasExternalPayment: boolean;
    lastExternalPaymentUrl?: string | null;
    isPaid: boolean;
    hasPaymentInProcess: boolean;
};

export const useRefreshOrderPaymentStatus = (
    order: OrderWithPaymentStatus | undefined,
    onStatusUpdated: () => void,
): void => {
    const [, updatePaymentStatusMutation] = useUpdatePaymentStatusMutation();
    const wasPaymentStatusRefreshAttemptedRef = useRef(false);
    const orderUuid = order?.uuid;
    const hasExternalPayment = order?.hasExternalPayment;
    const isPaid = order?.isPaid;
    const hasPaymentInProcess = order?.hasPaymentInProcess;
    const lastExternalPaymentUrl = order?.lastExternalPaymentUrl;

    const onStatusUpdatedEvent = useEffectEvent(onStatusUpdated);

    useEffect(() => {
        if (
            wasPaymentStatusRefreshAttemptedRef.current ||
            !orderUuid ||
            !hasOrderExternalPaymentContext({
                hasExternalPayment: !!hasExternalPayment,
                lastExternalPaymentUrl,
            }) ||
            isPaid ||
            !hasPaymentInProcess
        ) {
            return;
        }

        wasPaymentStatusRefreshAttemptedRef.current = true;

        void updatePaymentStatusMutation({
            orderUuid,
        }).then(() => {
            onStatusUpdatedEvent();
        });
    }, [
        hasExternalPayment,
        hasPaymentInProcess,
        isPaid,
        lastExternalPaymentUrl,
        orderUuid,
        updatePaymentStatusMutation,
    ]);
};
