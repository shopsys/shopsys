import { useUpdatePaymentStatusMutationApi } from 'graphql/generated';
import { onGtmCreateOrderEventHandler } from 'gtm/helpers/eventHandlers';
import { getGtmCreateOrderEventFromLocalStorage, removeGtmCreateOrderEventFromLocalStorage } from 'gtm/helpers/helpers';
import { useEffect } from 'react';

export const useUpdatePaymentStatus = (orderUuid: string, orderPaymentStatusPageValidityHash: string | null) => {
    const [{ data: paymentStatusData }, updatePaymentStatusMutation] = useUpdatePaymentStatusMutationApi();

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
        updatePaymentStatus();
    }, []);

    return paymentStatusData;
};
