import { getGtmPageViewEvent } from './getGtmPageViewEvent';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import { BreadcrumbFragment } from 'graphql/requests/breadcrumbs/fragments/BreadcrumbFragment.generated';
import { GtmPageType } from 'gtm/enums/GtmPageType';
import { getGtmPageInfoType } from 'gtm/helpers/getGtmPageInfoType';
import { useGtmCartInfo } from 'gtm/helpers/useGtmCartInfo';
import { GtmPageViewEventType } from 'gtm/types/events';
import { useCurrentUserContactInformation } from 'hooks/user/useCurrentUserContactInformation';
import { useMemo } from 'react';
import { usePersistStore } from 'store/usePersistStore';

export const useGtmStaticPageViewEvent = (
    pageType: GtmPageType,
    breadcrumbs?: BreadcrumbFragment[],
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
