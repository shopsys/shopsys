import { usePromotedCategoriesQueryApi } from 'graphql/generated';
import { useQueryError } from 'hooks/graphQl/useQueryError';
import { ListedCategoryType } from 'types/category';

export function usePromotedCategories(): ListedCategoryType[] | undefined {
    const [{ data, error }] = usePromotedCategoriesQueryApi();
    useQueryError(error);

    return data?.promotedCategories;
}
