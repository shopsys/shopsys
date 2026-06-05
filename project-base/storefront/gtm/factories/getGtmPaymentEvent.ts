import { GtmEventType } from 'gtm/enums/GtmEventType';
import { GtmPaymentEventType } from 'gtm/types/events';

export const getGtmPaymentEvent = (
    orderNumber: string,
    paymentName: string,
    isPaymentSuccessful: boolean,
    paymentRetryCount: number,
    paymentStatus?: string | null,
    paymentFalseReason?: string,
): GtmPaymentEventType => ({
    event: GtmEventType.payment,
    ecommerce: {
        id: orderNumber,
        isPaymentSuccessful,
        paymentRetryCount,
        paymentStatus,
        PaymentFalseReason: paymentFalseReason,
        paymentType: paymentName,
    },
    _clear: true,
});
