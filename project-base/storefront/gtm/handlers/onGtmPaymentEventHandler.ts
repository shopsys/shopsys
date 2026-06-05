import { getGtmPaymentEvent } from 'gtm/factories/getGtmPaymentEvent';
import { gtmSafePushEvent } from 'gtm/utils/gtmSafePushEvent';

export const onGtmPaymentTryEventHandler = (
    orderNumber: string,
    paymentName: string,
    isPaymentSuccessful?: boolean,
    paymentFalseReason?: string,
    paymentRetryCount: number = 0,
    paymentStatus?: string | null,
): void => {
    const normalizedIsPaymentSuccessful = isPaymentSuccessful === undefined ? true : isPaymentSuccessful;

    gtmSafePushEvent(
        getGtmPaymentEvent(
            orderNumber,
            paymentName,
            normalizedIsPaymentSuccessful,
            paymentRetryCount,
            paymentStatus ?? (normalizedIsPaymentSuccessful ? 'NOT_APPLICABLE' : undefined),
            paymentFalseReason,
        ),
    );
};
