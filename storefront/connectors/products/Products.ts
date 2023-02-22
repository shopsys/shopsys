import { ListedProductFragmentApi, usePromotedProductsQueryApi } from 'graphql/generated';
import { useQueryError } from 'hooks/graphQl/useQueryError';

export const usePromotedProducts = (): ListedProductFragmentApi[] | undefined => {
    const [{ data, error }] = usePromotedProductsQueryApi();
    useQueryError(error);

    return data?.promotedProducts;
};
