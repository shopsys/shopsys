import { GtmConsent } from 'gtm/enums/GtmConsent';
import { GtmConsentInfoType } from 'gtm/types/objects';
import { UserConsentFormType } from 'types/form';

export const getGtmConsentInfo = (userConsent: UserConsentFormType | null): GtmConsentInfoType => ({
    marketing: userConsent?.marketing ? GtmConsent.granted : GtmConsent.denied,
    statistics: userConsent?.statistics ? GtmConsent.granted : GtmConsent.denied,
    preferences: userConsent?.preferences ? GtmConsent.granted : GtmConsent.denied,
});
