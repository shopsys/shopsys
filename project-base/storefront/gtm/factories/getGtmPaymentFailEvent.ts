import { GtmEventType } from 'gtm/enums/GtmEventType';
import { GtmPaymentFailEventType } from 'gtm/types/events';

export const getGtmPaymentFailEvent = (orderId: string): GtmPaymentFailEventType => ({
    event: GtmEventType.payment_fail,
    paymentFail: {
        id: orderId,
    },
    _clear: true,
});
