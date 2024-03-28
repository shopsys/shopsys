import { TypeListedStoreFragment } from 'graphql/requests/stores/fragments/ListedStoreFragment.generated';
import { TypeTransportWithAvailablePaymentsAndStoresFragment } from 'graphql/requests/transports/fragments/TransportWithAvailablePaymentsAndStoresFragment.generated';
import { getGtmTransportChangeEvent } from 'gtm/factories/getGtmTransportChangeEvent';
import { GtmCartInfoType } from 'gtm/types/objects';
import { gtmSafePushEvent } from 'gtm/utils/gtmSafePushEvent';

export const onGtmTransportChangeEventHandler = (
    gtmCartInfo: GtmCartInfoType | undefined | null,
    updatedTransport: TypeTransportWithAvailablePaymentsAndStoresFragment | null,
    updatedPickupPlace: TypeListedStoreFragment | null,
    paymentName: string | undefined,
): void => {
    if (gtmCartInfo && updatedTransport !== null) {
        gtmSafePushEvent(getGtmTransportChangeEvent(gtmCartInfo, updatedTransport, updatedPickupPlace, paymentName));
    }
};
