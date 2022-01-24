import { ListedPickupPlaceFragmentApi, TransportWithAvailablePaymentsAndStoresFragmentApi } from 'graphql/generated';
import { getPacketeryCookie } from 'helpers/packetery';
import { PickupPlaceType } from 'types/pickupPlace';
import { TransportType } from 'types/transport';

export const getSelectedPickupPlace = (
    transport: TransportType | null,
    pickupPlaceIdentifier: string | null | undefined,
): PickupPlaceType | null => {
    if (transport === null || pickupPlaceIdentifier === null) {
        return null;
    }

    if (transport.transportType.code === 'packetery') {
        return getPacketeryCookie();
    }

    const pickupPlace = transport.stores.find((place) => place.identifier === pickupPlaceIdentifier);
    return pickupPlace === undefined ? null : pickupPlace;
};

const mapPickupPlaceApiData = (pickupPlace: ListedPickupPlaceFragmentApi): PickupPlaceType => {
    return {
        ...pickupPlace,
        identifier: pickupPlace.uuid,
        description: pickupPlace.description !== null ? pickupPlace.description : '',
        openingHours: pickupPlace.openingHoursHtml !== null ? pickupPlace.openingHoursHtml : '',
    };
};

export const mapPickupPlacesApiData = (
    storesConnectionApi: TransportWithAvailablePaymentsAndStoresFragmentApi['stores'],
): PickupPlaceType[] => {
    if (storesConnectionApi?.edges === undefined || storesConnectionApi.edges === null) {
        return [];
    }

    const mappedStores = [];
    for (const edge of storesConnectionApi.edges) {
        if (edge?.node !== undefined && edge.node !== null) {
            mappedStores.push(mapPickupPlaceApiData(edge.node));
        }
    }

    return mappedStores;
};
