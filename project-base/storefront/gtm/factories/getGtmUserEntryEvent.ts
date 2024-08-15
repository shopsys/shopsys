import { GtmEventType } from 'gtm/enums/GtmEventType';
import { mapGtmUserEntryInfoFromCurrentCustomer } from 'gtm/mappers/mapGtmUserEntryInfoFromCurrentCustomer';
import { GtmUserEntryEventType } from 'gtm/types/events';
import { CurrentCustomerType } from 'types/customer';

export const getGtmUserEntryEvent = (
    type: GtmEventType.login | GtmEventType.registration,
    user: CurrentCustomerType,
): GtmUserEntryEventType => ({
    event: type,
    user: mapGtmUserEntryInfoFromCurrentCustomer(user),
    _clear: true,
});
