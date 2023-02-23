import { ListedBrandFragmentApi, useBrandsQueryApi } from 'graphql/generated';
import { useQueryError } from 'hooks/graphQl/useQueryError';

export function useBrands(): ListedBrandFragmentApi[] | undefined {
    const [{ data, error }] = useBrandsQueryApi();
    useQueryError(error);

    return data?.brands;
}
