import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import type { GtmPageViewEventType } from 'gtm/types/events';
import { getGtmPageInfoTypeForFriendlyUrl } from 'gtm/utils/getGtmPageInfoTypeForFriendlyUrl';
import { useGtmCartInfo } from 'gtm/utils/useGtmCartInfo';
import { usePersistStore } from 'store/usePersistStore';
import type { FriendlyUrlPageType } from 'types/friendlyUrl';
import { useCurrentUserContactInformation } from 'utils/user/useCurrentUserContactInformation';
import { getGtmPageViewEvent } from './getGtmPageViewEvent';

export const useGtmFriendlyPageViewEvent = (
    friendlyUrlPageData: FriendlyUrlPageType | null | undefined,
): GtmPageViewEventType => {
    const { gtmCartInfo, isCartLoaded } = useGtmCartInfo();
    const domainConfig = useDomainConfig();
    const userContactInformation = useCurrentUserContactInformation();
    const user = useCurrentCustomerData();
    const userConsent = usePersistStore((store) => store.userConsent);

    return getGtmPageViewEvent(
        getGtmPageInfoTypeForFriendlyUrl(friendlyUrlPageData),
        gtmCartInfo,
        isCartLoaded,
        user,
        userContactInformation,
        domainConfig,
        userConsent,
    );
};
