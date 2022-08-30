import { mapListedCategoryApiData } from './Categories';
import { PromotedCategoriesQueryApi, usePromotedCategoriesQueryApi } from 'graphql/generated';
import { useQueryError } from 'hooks/graphQl/useQueryError';
import { ListedCategoryType } from 'types/category';

export function usePromotedCategories(): ListedCategoryType[] | undefined {
    const [{ data, error }] = usePromotedCategoriesQueryApi();
    useQueryError(error);

    if (data?.promotedCategories === undefined) {
        return undefined;
    }

    return mapCategoryApiData(data.promotedCategories);
}

const mapCategoryApiData = (apiData: PromotedCategoriesQueryApi['promotedCategories']): ListedCategoryType[] => {
    return apiData.map((apiCategory) => mapListedCategoryApiData(apiCategory));
};
