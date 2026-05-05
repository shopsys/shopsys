import { GtmEventType } from 'gtm/enums/GtmEventType';
import { GtmPageReadyEventType } from 'gtm/types/events';
import { GtmCartInfoType, GtmPageInfoType } from 'gtm/types/objects';
import { getGtmConsentInfo } from 'gtm/utils/getGtmConsentInfo';
import { getGtmDeviceType } from 'gtm/utils/getGtmDeviceType';
import { getGtmUserInfo } from 'gtm/utils/getGtmUserInfo';
import { ContactInformation } from 'store/slices/createContactInformationSlice';
import { CurrentCustomerType } from 'types/customer';
import { UserConsentFormType } from 'types/form';
import { DomainConfigType } from 'utils/domain/domainConfig';

export const getGtmPageReadyEvent = (
    pageInfo: GtmPageInfoType,
    gtmCartInfo: GtmCartInfoType | null,
    isCartLoaded: boolean,
    user: CurrentCustomerType | null | undefined,
    userContactInformation: ContactInformation,
    domainConfig: DomainConfigType,
    userConsent: UserConsentFormType | null,
): GtmPageReadyEventType => ({
    event: GtmEventType.page_ready,
    page: pageInfo,
    user: getGtmUserInfo(user, userContactInformation),
    device: getGtmDeviceType(),
    consent: getGtmConsentInfo(userConsent),
    currencyCode: domainConfig.currencyCode,
    language: domainConfig.defaultLocale,
    cart: gtmCartInfo,
    _clear: true,
    _isLoaded: isCartLoaded,
});
