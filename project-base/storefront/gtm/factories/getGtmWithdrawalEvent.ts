import { GtmEventType } from 'gtm/enums/GtmEventType';
import { GtmWithdrawalEventType } from 'gtm/types/events';

export const getGtmWithdrawalEvent = (orderNumber: string): GtmWithdrawalEventType => ({
    event: GtmEventType.withdrawal,
    ecommerce: {
        id: orderNumber,
    },
    _clear: true,
});
