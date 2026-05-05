import { getGtmWithdrawalEvent } from 'gtm/factories/getGtmWithdrawalEvent';
import { gtmSafePushEvent } from 'gtm/utils/gtmSafePushEvent';

export const onGtmWithdrawalEventHandler = (orderNumber: string): void => {
    gtmSafePushEvent(getGtmWithdrawalEvent(orderNumber));
};
