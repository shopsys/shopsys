import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import { useGtmContext } from 'gtm/context/GtmProvider';
import type { GtmPageReadyEventType } from 'gtm/types/events';
import { getGtmPageInfoTypeForFriendlyUrl } from 'gtm/utils/getGtmPageInfoTypeForFriendlyUrl';
import { useGtmCartInfo } from 'gtm/utils/useGtmCartInfo';
import { usePersistStore } from 'store/usePersistStore';
import type { FriendlyUrlPageType } from 'types/friendlyUrl';
import { useCurrentUserContactInformation } from 'utils/user/useCurrentUserContactInformation';
import { getGtmPageReadyEvent } from './getGtmPageReadyEvent';

export const useGtmFriendlyPageReadyEvent = (
    friendlyUrlPageData: FriendlyUrlPageType | null | undefined,
): GtmPageReadyEventType => {
    const { gtmCartInfo, isCartLoaded, pickupPlace } = useGtmCartInfo();
    const domainConfig = useDomainConfig();
    const userContactInformation = useCurrentUserContactInformation();
    const user = useCurrentCustomerData();
    const userConsent = usePersistStore((store) => store.userConsent);
    const { ipAddress } = useGtmContext();

    return getGtmPageReadyEvent(
        getGtmPageInfoTypeForFriendlyUrl(friendlyUrlPageData),
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
