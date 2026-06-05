import { useUpdatePaymentStatusMutation } from 'graphql/requests/orders/mutations/UpdatePaymentStatusMutation.generated';
import { getGtmPaymentEvent } from 'gtm/factories/getGtmPaymentEvent';
import {
    getGtmCreateOrderEventFromLocalStorage,
    removeGtmCreateOrderEventFromLocalStorage,
} from 'gtm/utils/gtmCreateOrderEventLocalStorage';
import {
    getGtmPaymentEventFromLocalStorage,
    removeGtmPaymentEventFromLocalStorage,
    saveGtmPaymentEventInLocalStorage,
} from 'gtm/utils/gtmPaymentEventLocalStorage';
import { gtmSafePushEvent } from 'gtm/utils/gtmSafePushEvent';
import { useEffect, useRef } from 'react';
import { getOrderPaymentItem } from 'utils/mappers/order';

export const useUpdatePaymentStatus = (
    orderUuid: string | undefined,
    shouldUpdatePaymentStatus: boolean,
    updateTrigger: string | null,
) => {
    const [
        { data: paymentStatusData, error: paymentStatusError, fetching: isPaymentStatusFetching },
        updatePaymentStatusMutation,
    ] = useUpdatePaymentStatusMutation();
    const lastPaymentStatusUpdateTriggerRef = useRef<string | null>(null);

    useEffect(() => {
        if (!shouldUpdatePaymentStatus || !orderUuid || updateTrigger === null) {
            return;
        }

        if (lastPaymentStatusUpdateTriggerRef.current === updateTrigger) {
            return;
        }

        const updatePaymentStatus = async () => {
            const updatePaymentStatusActionResult = await updatePaymentStatusMutation({
                orderUuid,
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
    }, [orderUuid, shouldUpdatePaymentStatus, updatePaymentStatusMutation, updateTrigger]);

    useEffect(() => {
        if (paymentStatusData) {
            const { isPaid, items, number } = paymentStatusData.UpdatePaymentStatus;
            const paymentItem = getOrderPaymentItem(items);
            const { gtmPaymentEvent } = getGtmPaymentEventFromLocalStorage();

            const retryCount = gtmPaymentEvent ? gtmPaymentEvent.ecommerce.paymentRetryCount + 1 : 0;
            const newGtmPaymentEvent = getGtmPaymentEvent(number, paymentItem?.payment?.name || '', isPaid, retryCount);

            gtmSafePushEvent(newGtmPaymentEvent);

            if (!isPaid) {
                saveGtmPaymentEventInLocalStorage(newGtmPaymentEvent);
            } else {
                removeGtmPaymentEventFromLocalStorage();
            }
        }
    }, [paymentStatusData]);

    return {
        data: paymentStatusData,
        error: paymentStatusError,
        fetching: isPaymentStatusFetching,
    };
};
