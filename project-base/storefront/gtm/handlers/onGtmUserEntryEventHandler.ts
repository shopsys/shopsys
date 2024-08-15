import { GtmEventType } from 'gtm/enums/GtmEventType';
import { getGtmUserEntryEvent } from 'gtm/factories/getGtmUserEntryEvent';
import { gtmSafePushEvent } from 'gtm/utils/gtmSafePushEvent';
import { CurrentCustomerType } from 'types/customer';

export const onGtmUserEntryEventHandler = (
    type: GtmEventType.login | GtmEventType.registration,
    user: CurrentCustomerType | null | undefined,
): void => {
    if (!user) {
        return;
    }

    gtmSafePushEvent(getGtmUserEntryEvent(type, user));
};
