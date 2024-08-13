import { TypeTransportWithAvailablePaymentsAndStoresFragment } from 'graphql/requests/transports/fragments/TransportWithAvailablePaymentsAndStoresFragment.generated';
import { getGtmTransportChangeEvent } from 'gtm/factories/getGtmTransportChangeEvent';
import { GtmCartInfoType } from 'gtm/types/objects';
import { gtmSafePushEvent } from 'gtm/utils/gtmSafePushEvent';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';

export const onGtmTransportChangeEventHandler = (
    gtmCartInfo: GtmCartInfoType | undefined | null,
    updatedTransport: TypeTransportWithAvailablePaymentsAndStoresFragment | null,
    updatedPickupPlace: StoreOrPacketeryPoint | null,
    paymentName: string | undefined,
    arePricesHidden: boolean,
): void => {
    if (gtmCartInfo && updatedTransport !== null) {
        gtmSafePushEvent(
            getGtmTransportChangeEvent(gtmCartInfo, updatedTransport, updatedPickupPlace, paymentName, arePricesHidden),
        );
    }
};
