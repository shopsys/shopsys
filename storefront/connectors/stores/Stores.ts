import { mapConnectionEdges } from 'connectors/connection/Connection';
import { ListedStoreFragmentApi, useStoresQueryApi } from 'graphql/generated';
import { useQueryError } from 'hooks/graphQl/useQueryError';

export function useStores(): ListedStoreFragmentApi[] {
    const [{ data, error }] = useStoresQueryApi();
    useQueryError(error);

    if (data?.stores === undefined) {
        return [];
    }

    return mapConnectionEdges(data.stores.edges);
}
