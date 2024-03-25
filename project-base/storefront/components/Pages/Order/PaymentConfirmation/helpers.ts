import { useUpdatePaymentStatusMutation } from 'graphql/requests/orders/mutations/UpdatePaymentStatusMutation.generated';
import { onGtmCreateOrderEventHandler } from 'gtm/handlers/onGtmCreateOrderEventHandler';
import {
    getGtmCreateOrderEventFromLocalStorage,
    removeGtmCreateOrderEventFromLocalStorage,
} from 'gtm/helpers/gtmCreateOrderEventLocalStorage';
import { useEffect, useRef } from 'react';

export const useUpdatePaymentStatus = (orderUuid: string, orderPaymentStatusPageValidityHash: string | null) => {
    const [{ data: paymentStatusData }, updatePaymentStatusMutation] = useUpdatePaymentStatusMutation();
    const wasPaymentStatusUpdatedRef = useRef(false);

    const updatePaymentStatus = async () => {
        const updatePaymentStatusActionResult = await updatePaymentStatusMutation({
            orderUuid,
            orderPaymentStatusPageValidityHash,
        });

        const { gtmCreateOrderEventOrderPart, gtmCreateOrderEventUserPart } = getGtmCreateOrderEventFromLocalStorage();
        if (
            !updatePaymentStatusActionResult.data?.UpdatePaymentStatus ||
            !gtmCreateOrderEventOrderPart ||
            !gtmCreateOrderEventUserPart
        ) {
            return;
        }

        onGtmCreateOrderEventHandler(
            gtmCreateOrderEventOrderPart,
            gtmCreateOrderEventUserPart,
            updatePaymentStatusActionResult.data.UpdatePaymentStatus.isPaid,
        );
        removeGtmCreateOrderEventFromLocalStorage();
    };

    useEffect(() => {
        if (!wasPaymentStatusUpdatedRef.current) {
            updatePaymentStatus();
            wasPaymentStatusUpdatedRef.current = true;
        }
    }, []);

    return paymentStatusData;
};
