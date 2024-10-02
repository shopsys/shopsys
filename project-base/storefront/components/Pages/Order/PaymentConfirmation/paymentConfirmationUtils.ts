import { useUpdatePaymentStatusMutation } from 'graphql/requests/orders/mutations/UpdatePaymentStatusMutation.generated';
import { onGtmCreateOrderEventHandler } from 'gtm/handlers/onGtmCreateOrderEventHandler';
import {
    getGtmCreateOrderEventFromLocalStorage,
    removeGtmCreateOrderEventFromLocalStorage,
} from 'gtm/utils/gtmCreateOrderEventLocalStorage';
import { Translate } from 'next-translate';
import { useEffect, useRef } from 'react';
import { CombinedError } from 'urql';
import { getUserFriendlyErrors } from 'utils/errors/friendlyErrorMessageParser';

export const getPaymentSessionExpiredErrorMessage = (
    isOrderPaymentFailedError: CombinedError | undefined,
    t: Translate,
) => {
    if (!isOrderPaymentFailedError?.graphQLErrors.length) {
        return '';
    }

    const { applicationError } = getUserFriendlyErrors(isOrderPaymentFailedError, t);
    return applicationError?.type === 'order-sent-page-not-available' ? t('Order sent page is not available.') : '';
};

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
