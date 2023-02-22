import { ListedStoreConnectionFragmentApi, useStoresQueryApi } from 'graphql/generated';
import { useQueryError } from 'hooks/graphQl/useQueryError';

export function useStores(): ListedStoreConnectionFragmentApi | undefined {
    const [{ data, error }] = useStoresQueryApi();
    useQueryError(error);

    return data?.stores;
}
