import { ListedStoreFragmentApi, TransportWithAvailablePaymentsAndStoresFragmentApi } from 'graphql/generated';
import { getPacketeryCookie } from 'helpers/packetery';

export const getSelectedPickupPlace = (
    transport: TransportWithAvailablePaymentsAndStoresFragmentApi | null,
    pickupPlaceIdentifier: string | null | undefined,
): ListedStoreFragmentApi | null => {
    if (transport === null || pickupPlaceIdentifier === null) {
        return null;
    }

    if (transport.transportType.code === 'packetery') {
        return getPacketeryCookie();
    }

    const pickupPlace = transport.stores?.edges?.find(
        (pickupPlaceNode) => pickupPlaceNode?.node?.identifier === pickupPlaceIdentifier,
    );

    return pickupPlace?.node === undefined ? null : pickupPlace.node;
};
