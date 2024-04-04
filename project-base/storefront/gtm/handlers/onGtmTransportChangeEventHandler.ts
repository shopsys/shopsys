import { ListedStoreFragment } from 'graphql/requests/stores/fragments/ListedStoreFragment.generated';
import { TransportWithAvailablePaymentsAndStoresFragment } from 'graphql/requests/transports/fragments/TransportWithAvailablePaymentsAndStoresFragment.generated';
import { getGtmTransportChangeEvent } from 'gtm/factories/getGtmTransportChangeEvent';
import { GtmCartInfoType } from 'gtm/types/objects';
import { gtmSafePushEvent } from 'gtm/utils/gtmSafePushEvent';

export const onGtmTransportChangeEventHandler = (
    gtmCartInfo: GtmCartInfoType | undefined | null,
    updatedTransport: TransportWithAvailablePaymentsAndStoresFragment | null,
    updatedPickupPlace: ListedStoreFragment | null,
    paymentName: string | undefined,
): void => {
    if (gtmCartInfo && updatedTransport !== null) {
        gtmSafePushEvent(getGtmTransportChangeEvent(gtmCartInfo, updatedTransport, updatedPickupPlace, paymentName));
    }
};
