import { useUpdatePaymentStatusMutation } from 'graphql/requests/orders/mutations/UpdatePaymentStatusMutation.generated';
import {
    getGtmCreateOrderEventFromLocalStorage,
    removeGtmCreateOrderEventFromLocalStorage,
} from 'gtm/utils/gtmCreateOrderEventLocalStorage';
import { gtmSafePushEvent } from 'gtm/utils/gtmSafePushEvent';
import { getGtmPaymentEventFromPaymentStatusUpdate } from 'gtm/utils/paymentGtmEventUtils';
import { useEffect, useRef } from 'react';

export const useUpdatePaymentStatus = (
    orderUuid: string | undefined,
    orderUrlHash: string | null,
    shouldUpdatePaymentStatus: boolean,
    updateTrigger: string | null,
) => {
    const [
        { data: paymentStatusData, error: paymentStatusError, fetching: isPaymentStatusFetching },
        updatePaymentStatusMutation,
    ] = useUpdatePaymentStatusMutation();
    const lastPaymentStatusUpdateTriggerRef = useRef<string | null>(null);

    useEffect(() => {
        if (!shouldUpdatePaymentStatus || !orderUuid || !orderUrlHash || updateTrigger === null) {
            return;
        }

        if (lastPaymentStatusUpdateTriggerRef.current === updateTrigger) {
            return;
        }

        const updatePaymentStatus = async () => {
            const updatePaymentStatusActionResult = await updatePaymentStatusMutation({
                orderUuid,
                orderUrlHash,
            });

            const { gtmCreateOrderEventOrderPart, gtmCreateOrderEventUserPart } =
                getGtmCreateOrderEventFromLocalStorage();
            if (
                !updatePaymentStatusActionResult.data?.UpdatePaymentStatus ||
                !gtmCreateOrderEventOrderPart ||
                !gtmCreateOrderEventUserPart
            ) {
                return;
            }

            removeGtmCreateOrderEventFromLocalStorage();
        };

        void updatePaymentStatus();
        lastPaymentStatusUpdateTriggerRef.current = updateTrigger;
    }, [orderUuid, orderUrlHash, shouldUpdatePaymentStatus, updatePaymentStatusMutation, updateTrigger]);

    useEffect(() => {
        if (paymentStatusData) {
            gtmSafePushEvent(getGtmPaymentEventFromPaymentStatusUpdate(paymentStatusData.UpdatePaymentStatus));
        }
    }, [paymentStatusData]);

    return {
        data: paymentStatusData,
        error: paymentStatusError,
        fetching: isPaymentStatusFetching,
    };
};
