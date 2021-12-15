import { getPacketeryCookie } from 'helpers/packetery';
import { PickupPlaceType } from 'types/pickupPlace';
import { TransportType } from 'types/transport';
import { TransportWithAvailablePaymentsAndStoresFragmentApi } from 'graphql/generated';

export const getSelectedPickupPlace = (
    transport: TransportType | null,
    pickupPlaceIdentifier: string | null,
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

export const mapPickupPlacesApiData = (
    storesConnectionApi: TransportWithAvailablePaymentsAndStoresFragmentApi['stores'],
): PickupPlaceType[] => {
    if (storesConnectionApi?.edges === undefined || storesConnectionApi.edges === null) {
        return [];
    }

    const mappedStores = [];
    for (const edge of storesConnectionApi.edges) {
        if (edge?.node !== undefined && edge.node !== null) {
            mappedStores.push({
                ...edge.node,
                identifier: edge.node.uuid,
                description:
                    edge.node.description !== undefined && edge.node.description !== null ? edge.node.description : '',
                openingHours:
                    edge.node.openingHoursHtml !== undefined && edge.node.openingHoursHtml !== null
                        ? edge.node.openingHoursHtml
                        : '',
            });
        }
    }

    return mappedStores;
};
