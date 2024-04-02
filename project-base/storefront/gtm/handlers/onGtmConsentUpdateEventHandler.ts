import { getGtmConsentUpdateEvent } from 'gtm/factories/getGtmConsentUpdateEvent';
import { GtmConsentInfoType } from 'gtm/types/objects';
import { gtmSafePushEvent } from 'gtm/utils/gtmSafePushEvent';

export const onGtmConsentUpdateEventHandler = (gtmConsentInfo: GtmConsentInfoType): void => {
    gtmSafePushEvent(getGtmConsentUpdateEvent(gtmConsentInfo));
};
