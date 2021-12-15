import { StoresQueryApi, useStoresQueryApi } from 'graphql/generated';
import { StoreListType } from 'types/store';
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
