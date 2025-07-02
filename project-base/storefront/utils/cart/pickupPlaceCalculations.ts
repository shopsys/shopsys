import { TypeLastOrderFragment } from 'graphql/requests/orders/fragments/LastOrderFragment.generated';
import { TypeTransportWithAvailablePaymentsAndStoresFragment } from 'graphql/requests/transports/fragments/TransportWithAvailablePaymentsAndStoresFragment.generated';
import { Maybe } from 'graphql/types';
import { getGtmPickupPlaceFromLastOrder } from 'gtm/mappers/getGtmPickupPlaceFromLastOrder';
import { getGtmPickupPlaceFromStore } from 'gtm/mappers/getGtmPickupPlaceFromStore';
import { isPacketeryTransport } from 'utils/packetery';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';

export const PICKUP_POINT_NOT_SET_ERROR_MESSAGE = 'Packetery pickup point is not set';

export const getLastOrderPickupPlace = (
    lastOrder: TypeLastOrderFragment,
    lastOrderPickupPlaceIdentifier: string,
    lastOrderPickupPlaceFromApi: StoreOrPacketeryPoint | undefined | null,
    packeteryPickupPoint: StoreOrPacketeryPoint | null,
): StoreOrPacketeryPoint | null => {
    if (packeteryPickupPoint?.identifier === lastOrderPickupPlaceIdentifier) {
        return packeteryPickupPoint;
    }

    if (lastOrderPickupPlaceFromApi?.identifier) {
        return getGtmPickupPlaceFromStore(lastOrderPickupPlaceFromApi);
    }

    if (!packeteryPickupPoint) {
        throw new Error(PICKUP_POINT_NOT_SET_ERROR_MESSAGE);
    }

    return getGtmPickupPlaceFromLastOrder(lastOrderPickupPlaceIdentifier, lastOrder);
};

export const getSelectedPickupPlace = (
    transport: Maybe<TypeTransportWithAvailablePaymentsAndStoresFragment> | undefined,
    pickupPlaceIdentifier: string | null | undefined,
    packeteryPickupPoint: StoreOrPacketeryPoint | null,
): StoreOrPacketeryPoint | null => {
    if (!transport || !pickupPlaceIdentifier) {
        return null;
    }

    if (isPacketeryTransport(transport.transportTypeCode)) {
        return packeteryPickupPoint;
    }

    const pickupPlace = transport.stores?.edges?.find(
        (pickupPlaceNode) => pickupPlaceNode?.node?.identifier === pickupPlaceIdentifier,
    );

    return pickupPlace?.node === undefined ? null : pickupPlace.node;
};
