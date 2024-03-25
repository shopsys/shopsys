import { GtmEventType } from 'gtm/enums/GtmEventType';
import { GtmConsentUpdateEventType } from 'gtm/types/events';
import { GtmConsentInfoType } from 'gtm/types/objects';

export const getGtmConsentUpdateEvent = (updatedGtmConsentInfo: GtmConsentInfoType): GtmConsentUpdateEventType => ({
    event: GtmEventType.consent_update,
    consent: updatedGtmConsentInfo,
    _clear: true,
});
