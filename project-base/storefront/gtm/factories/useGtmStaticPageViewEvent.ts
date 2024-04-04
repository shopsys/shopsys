import { getGtmPageViewEvent } from './getGtmPageViewEvent';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import { TypeBreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { GtmPageViewEventType } from 'gtm/types/events';
import { getGtmPageInfoType } from 'gtm/utils/getGtmPageInfoType';
import { useGtmCartInfo } from 'gtm/utils/useGtmCartInfo';
import { useMemo } from 'react';
import { usePersistStore } from 'store/usePersistStore';
import { useCurrentUserContactInformation } from 'utils/user/useCurrentUserContactInformation';

export const useGtmStaticPageViewEvent = (
    pageType: GtmPageType,
    breadcrumbs?: TypeBreadcrumbFragment[],
): GtmPageViewEventType => {
    const { gtmCartInfo, isCartLoaded } = useGtmCartInfo();
    const domainConfig = useDomainConfig();
    const userContactInformation = useCurrentUserContactInformation();
    const user = useCurrentCustomerData();
    const userConsent = usePersistStore((store) => store.userConsent);

    return useMemo(
        () =>
            getGtmPageViewEvent(
                getGtmPageInfoType(pageType, breadcrumbs),
                gtmCartInfo,
                isCartLoaded,
                user,
                userContactInformation,
                domainConfig,
                userConsent,
            ),
        [pageType, breadcrumbs, gtmCartInfo, isCartLoaded, user, userContactInformation, domainConfig, userConsent],
    );
};
