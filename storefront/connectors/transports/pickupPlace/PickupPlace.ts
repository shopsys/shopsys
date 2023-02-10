import {
    ListedStoreConnectionFragmentApi,
    ListedStoreFragmentApi,
    TransportWithAvailablePaymentsAndStoresFragmentApi,
} from 'graphql/generated';
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

export const mapPickupPlacesApiData = (
    storesConnectionApi: ListedStoreConnectionFragmentApi,
): ListedStoreFragmentApi[] => {
    if (storesConnectionApi.edges === null) {
        return [];
    }

    const mappedStores = [];
    for (const edge of storesConnectionApi.edges) {
        if (edge?.node !== undefined && edge.node !== null) {
            mappedStores.push(edge.node);
        }
    }

    return mappedStores;
};
