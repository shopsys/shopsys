import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import { TypeBreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';
import { useGtmContext } from 'gtm/context/GtmProvider';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { GtmPageReadyEventType } from 'gtm/types/events';
import { getGtmPageInfoType } from 'gtm/utils/getGtmPageInfoType';
import { useGtmCartInfo } from 'gtm/utils/useGtmCartInfo';
import { ContactInformation } from 'store/slices/createContactInformationSlice';
import { usePersistStore } from 'store/usePersistStore';
import { useCurrentUserContactInformation } from 'utils/user/useCurrentUserContactInformation';
import { getGtmPageReadyEvent } from './getGtmPageReadyEvent';

type UseGtmStaticPageReadyEventOptions = {
    userContactInformation?: ContactInformation;
};

export const useGtmStaticPageReadyEvent = (
    pageType: GtmPageType,
    breadcrumbs?: TypeBreadcrumbFragment[],
    options?: UseGtmStaticPageReadyEventOptions,
): GtmPageReadyEventType => {
    const { gtmCartInfo, isCartLoaded, pickupPlace } = useGtmCartInfo();
    const domainConfig = useDomainConfig();
    const currentUserContactInformation = useCurrentUserContactInformation();
    const userContactInformation = options?.userContactInformation ?? currentUserContactInformation;
    const user = useCurrentCustomerData();
    const userConsent = usePersistStore((store) => store.userConsent);
    const { ipAddress } = useGtmContext();

    return getGtmPageReadyEvent(
        getGtmPageInfoType(pageType, breadcrumbs),
        gtmCartInfo,
        isCartLoaded,
        user,
        userContactInformation,
        domainConfig,
        userConsent,
        pickupPlace,
        ipAddress,
    );
};
