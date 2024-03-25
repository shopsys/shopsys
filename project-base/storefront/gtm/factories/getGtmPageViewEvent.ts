import { GtmEventType } from 'gtm/enums/GtmEventType';
import { getGtmConsentInfo } from 'gtm/helpers/getGtmConsentInfo';
import { getGtmDeviceType } from 'gtm/helpers/getGtmDeviceType';
import { getGtmUserInfo } from 'gtm/helpers/getGtmUserInfo';
import { GtmPageViewEventType } from 'gtm/types/events';
import { GtmPageInfoType, GtmCartInfoType } from 'gtm/types/objects';
import { DomainConfigType } from 'helpers/domain/domainConfig';
import { ContactInformation } from 'store/slices/createContactInformationSlice';
import { CurrentCustomerType } from 'types/customer';
import { UserConsentFormType } from 'types/form';

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
