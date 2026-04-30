import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import { TypeBreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { GtmPageReadyEventType } from 'gtm/types/events';
import { getGtmPageInfoType } from 'gtm/utils/getGtmPageInfoType';
import { useGtmCartInfo } from 'gtm/utils/useGtmCartInfo';
import { usePersistStore } from 'store/usePersistStore';
import { useCurrentUserContactInformation } from 'utils/user/useCurrentUserContactInformation';
import { getGtmPageReadyEvent } from './getGtmPageReadyEvent';

export const useGtmStaticPageReadyEvent = (
    pageType: GtmPageType,
    breadcrumbs?: TypeBreadcrumbFragment[],
): GtmPageReadyEventType => {
    const { gtmCartInfo, isCartLoaded } = useGtmCartInfo();
    const domainConfig = useDomainConfig();
    const userContactInformation = useCurrentUserContactInformation();
    const user = useCurrentCustomerData();
    const userConsent = usePersistStore((store) => store.userConsent);

    return getGtmPageReadyEvent(
        getGtmPageInfoType(pageType, breadcrumbs),
        gtmCartInfo,
        isCartLoaded,
        user,
        userContactInformation,
        domainConfig,
        userConsent,
    );
};
