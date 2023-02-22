import { useBrandsQueryApi } from 'graphql/generated';
import { useQueryError } from 'hooks/graphQl/useQueryError';
import { ListedBrandType } from 'types/brand';

export function useBrands(): ListedBrandType[] | undefined {
    const [{ data, error }] = useBrandsQueryApi();
    useQueryError(error);

    return data?.brands;
}
