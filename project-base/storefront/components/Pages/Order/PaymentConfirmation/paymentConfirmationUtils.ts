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

        removeGtmCreateOrderEventFromLocalStorage();
    };

    useEffect(() => {
        if (!wasPaymentStatusUpdatedRef.current) {
            updatePaymentStatus();
            wasPaymentStatusUpdatedRef.current = true;
        }
    }, []);

    useEffect(() => {
        if (paymentStatusData) {
            const { isPaid, payment, number } = paymentStatusData.UpdatePaymentStatus;
            const { gtmPaymentEvent } = getGtmPaymentEventFromLocalStorage();

            const retryCount = gtmPaymentEvent ? gtmPaymentEvent.ecommerce.paymentRetryCount + 1 : 0;
            const newGtmPaymentEvent = getGtmPaymentEvent(number, payment.name, isPaid, retryCount);

            gtmSafePushEvent(newGtmPaymentEvent);

            if (!isPaid) {
                saveGtmPaymentEventInLocalStorage(newGtmPaymentEvent);
            } else {
                removeGtmPaymentEventFromLocalStorage();
            }
        }
    }, [paymentStatusData]);

    return paymentStatusData;
};
