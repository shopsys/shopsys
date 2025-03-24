import { getGtmPaymentEvent } from 'gtm/factories/getGtmPaymentEvent';
import { gtmSafePushEvent } from 'gtm/utils/gtmSafePushEvent';

export const onGtmPaymentTryEventHandler = (
    orderNumber: string,
    paymentName: string,
    isPaymentSuccessful?: boolean,
    paymentFalseReason?: string,
    paymentRetryCount: number = 0,
): void => {
    gtmSafePushEvent(
        getGtmPaymentEvent(
            orderNumber,
            paymentName,
            isPaymentSuccessful === undefined ? true : isPaymentSuccessful,
            paymentRetryCount,
            paymentFalseReason,
        ),
    );
};
