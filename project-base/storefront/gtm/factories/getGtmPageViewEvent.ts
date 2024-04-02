import { GtmEventType } from 'gtm/enums/GtmEventType';
import { GtmPageViewEventType } from 'gtm/types/events';
import { GtmPageInfoType, GtmCartInfoType } from 'gtm/types/objects';
import { getGtmConsentInfo } from 'gtm/utils/getGtmConsentInfo';
import { getGtmDeviceType } from 'gtm/utils/getGtmDeviceType';
import { getGtmUserInfo } from 'gtm/utils/getGtmUserInfo';
import { ContactInformation } from 'store/slices/createContactInformationSlice';
import { CurrentCustomerType } from 'types/customer';
import { UserConsentFormType } from 'types/form';
import { DomainConfigType } from 'utils/domain/domainConfig';

export const getGtmPageViewEvent = (
    pageInfo: GtmPageInfoType,
    gtmCartInfo: GtmCartInfoType | null,
    isCartLoaded: boolean,
    user: CurrentCustomerType | null | undefined,
    userContactInformation: ContactInformation,
    domainConfig: DomainConfigType,
    userConsent: UserConsentFormType | null,
): GtmPageViewEventType => ({
    event: GtmEventType.page_view,
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
