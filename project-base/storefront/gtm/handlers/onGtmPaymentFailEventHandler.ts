import { getGtmPaymentFailEvent } from 'gtm/factories/getGtmPaymentFailEvent';
import { gtmSafePushEvent } from 'gtm/utils/gtmSafePushEvent';

export const onGtmPaymentFailEventHandler = (orderId: string): void => {
    gtmSafePushEvent(getGtmPaymentFailEvent(orderId));
};
