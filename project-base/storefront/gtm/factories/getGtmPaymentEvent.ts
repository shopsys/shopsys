import { GtmEventType } from 'gtm/enums/GtmEventType';
import { GtmPaymentEventType } from 'gtm/types/events';

export const getGtmPaymentEvent = (
    orderNumber: string,
    paymentName: string,
    isPaymentSuccessful: boolean,
    paymentRetryCount: number,
    paymentFalseReason?: string,
): GtmPaymentEventType => ({
    event: GtmEventType.payment,
    ecommerce: {
        id: orderNumber,
        isPaymentSuccessful,
        paymentRetryCount,
        // PascalCase key name is dictated by GTM dataLayer schema — do not rename
        PaymentFalseReason: paymentFalseReason,
        paymentType: paymentName,
    },
    // Standard GTM pattern: clears previous ecommerce object from dataLayer to prevent bleed-through
    _clear: true,
});
