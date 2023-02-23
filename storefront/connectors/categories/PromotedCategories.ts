import { ListedCategoryFragmentApi, usePromotedCategoriesQueryApi } from 'graphql/generated';
import { useQueryError } from 'hooks/graphQl/useQueryError';

export function usePromotedCategories(): ListedCategoryFragmentApi[] | undefined {
    const [{ data, error }] = usePromotedCategoriesQueryApi();
    useQueryError(error);

    return data?.promotedCategories;
}
