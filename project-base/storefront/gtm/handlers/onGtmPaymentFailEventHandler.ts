import { getGtmPaymentFailEvent } from 'gtm/factories/getGtmPaymentFailEvent';
import { gtmSafePushEvent } from 'gtm/helpers/gtmSafePushEvent';

export const onGtmPaymentFailEventHandler = (orderId: string): void => {
    gtmSafePushEvent(getGtmPaymentFailEvent(orderId));
};
