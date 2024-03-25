import { getGtmPageViewEvent } from './getGtmPageViewEvent';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import { getGtmPageInfoTypeForFriendlyUrl } from 'gtm/helpers/getGtmPageInfoTypeForFriendlyUrl';
import { useGtmCartInfo } from 'gtm/helpers/useGtmCartInfo';
import { GtmPageViewEventType } from 'gtm/types/events';
import { useCurrentUserContactInformation } from 'hooks/user/useCurrentUserContactInformation';
import { useMemo } from 'react';
import { usePersistStore } from 'store/usePersistStore';
import { FriendlyUrlPageType } from 'types/friendlyUrl';

export const useGtmFriendlyPageViewEvent = (
    friendlyUrlPageData: FriendlyUrlPageType | null | undefined,
): GtmPageViewEventType => {
    const { gtmCartInfo, isCartLoaded } = useGtmCartInfo();
    const domainConfig = useDomainConfig();
    const userContactInformation = useCurrentUserContactInformation();
    const user = useCurrentCustomerData();
    const userConsent = usePersistStore((store) => store.userConsent);

    return useMemo(
        () =>
            getGtmPageViewEvent(
                getGtmPageInfoTypeForFriendlyUrl(friendlyUrlPageData),
                gtmCartInfo,
                isCartLoaded,
                user,
                userContactInformation,
                domainConfig,
                userConsent,
            ),
        [friendlyUrlPageData, gtmCartInfo, isCartLoaded, user, userContactInformation, domainConfig, userConsent],
    );
};
