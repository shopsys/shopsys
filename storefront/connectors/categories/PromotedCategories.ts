import { ImagesDefaultFragmentApi, PromotedCategoriesQueryApi, usePromotedCategoriesQueryApi } from 'graphql/generated';
import { CategoryItemType } from 'components/Blocks/Categories/CategoryItem/types';
import { ImageType } from 'components/Basic/Image/types';
import { mapImageSizeApiData } from 'connectors/image/size/ImageSize';
import { useQueryError } from 'hooks/graphQl/UseQueryError';

export function getPromotedCategories(): CategoryItemType[] | undefined {
    const [{ data, error }] = usePromotedCategoriesQueryApi();
    useQueryError(error);

    if (data?.promotedCategories === undefined) {
        return undefined;
    }

    return mapCategoryApiData(data.promotedCategories);
}

const mapCategoryApiData = (apiData: PromotedCategoriesQueryApi['promotedCategories']): CategoryItemType[] => {
    return apiData.map((apiCategory) => {
        return {
            ...apiCategory,
            name: apiCategory.name === undefined || apiCategory.name === null ? '' : apiCategory.name,
            image: mapCategoryImageApiData(apiCategory.images),
        };
    });
};

const mapCategoryImageApiData = (apiData: ImagesDefaultFragmentApi['images']): ImageType | null => {
    if (!(0 in apiData)) {
        return null;
    }
    return mapImageSizeApiData(apiData[0].sizes[0]);
};
