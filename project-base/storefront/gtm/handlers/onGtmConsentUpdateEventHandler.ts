import { getGtmConsentUpdateEvent } from 'gtm/factories/getGtmConsentUpdateEvent';
import { gtmSafePushEvent } from 'gtm/helpers/gtmSafePushEvent';
import { GtmConsentInfoType } from 'gtm/types/objects';

export const onGtmConsentUpdateEventHandler = (gtmConsentInfo: GtmConsentInfoType): void => {
    gtmSafePushEvent(getGtmConsentUpdateEvent(gtmConsentInfo));
};
