import { ListedStoreConnectionFragmentApi, ListedStoreFragmentApi, useStoresQueryApi } from 'graphql/generated';
import { ListedStoreType } from 'types/store';
import { useQueryError } from 'hooks/graphQl/UseQueryError';

export function getStores(): ListedStoreType[] {
    const [{ data, error }] = useStoresQueryApi();
    useQueryError(error);

    if (data?.stores === undefined) {
        return [];
    }

    return mapStoresApiData(data.stores);
}

const mapStoreApiData = (apiData: ListedStoreFragmentApi): ListedStoreType => {
    return {
        ...apiData,
        locationLatitude: apiData.locationLatitude !== null ? Number.parseFloat(apiData.locationLatitude) : null,
        locationLongitude: apiData.locationLongitude !== null ? Number.parseFloat(apiData.locationLongitude) : null,
        address: apiData.street + '<br />' + apiData.postcode + ' ' + apiData.city,
    };
};

const mapStoresApiData = (data: ListedStoreConnectionFragmentApi): ListedStoreType[] => {
    if (data.edges === null) {
        return [];
    }

    const mappedStores = [];

    for (const edge of data.edges) {
        if (edge?.node === undefined || edge.node === null) {
            continue;
        }

        mappedStores.push(mapStoreApiData(edge.node));
    }

    return mappedStores;
};
