import {
    StoresQueryApi,
    TransportWithAvailablePaymentsAndStoresFragmentApi,
    useStoresQueryApi,
} from 'graphql/generated';
import { StoreListType } from 'connectors/stores/types';
import { StoreType } from 'connectors/transports/types';
import { useQueryError } from 'hooks/graphQl/UseQueryError';

export function getStores(): StoreListType[] {
    const [{ data, error }] = useStoresQueryApi();
    useQueryError(error);

    if (data?.stores === undefined) {
        return [];
    }

    return mapStoresApiData(data.stores);
}

const mapStoresApiData = (data: StoresQueryApi['stores']): StoreListType[] => {
    if (data?.edges === undefined || data.edges === null) {
        return [];
    }

    const mappedStores = [];

    for (const edge of data.edges) {
        if (edge?.node === undefined || edge?.node === null) {
            continue;
        }

        const mappedStore: StoreListType = {
            slug: edge.node.slug,
            name: edge.node.name,
            locationLatitude:
                edge.node.locationLatitude !== undefined && edge.node.locationLatitude !== null
                    ? Number.parseFloat(edge.node.locationLatitude)
                    : null,
            locationLongitude:
                edge.node.locationLongitude !== undefined && edge.node.locationLongitude !== null
                    ? Number.parseFloat(edge.node.locationLongitude)
                    : null,
            address: edge.node.street + '<br />' + edge.node.postcode + ' ' + edge.node.city,
            openingHours: edge.node.openingHours,
        };

        mappedStores.push(mappedStore);
    }

    return mappedStores;
};

export const mapStoresListApiData = (
    storesConnectionApi: TransportWithAvailablePaymentsAndStoresFragmentApi['stores'],
): StoreType[] => {
    if (storesConnectionApi?.edges === undefined || storesConnectionApi.edges === null) {
        return [];
    }

    const mappedStores = [];
    for (const edge of storesConnectionApi.edges) {
        if (edge?.node !== undefined && edge.node !== null) {
            mappedStores.push({
                ...edge.node,
                description:
                    edge.node.description !== undefined && edge.node.description !== null ? edge.node.description : '',
                openingHours:
                    edge.node.openingHours !== undefined && edge.node.openingHours !== null
                        ? edge.node.openingHours
                        : '',
            });
        }
    }

    return mappedStores;
};
