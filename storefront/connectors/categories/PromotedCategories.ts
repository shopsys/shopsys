import { ImagesDefaultFragmentApi, PromotedCategoriesQueryApi, usePromotedCategoriesQueryApi } from 'graphql/generated';
import { CategoryItemType } from 'components/Blocks/Categories/CategoryItem/types';
import { getUserFriendlyErrors } from 'connectors/lib/friendlyErrorMessageParser';
import { ImageType } from 'components/Basic/Image/types';
import { mapImageSizeApiData } from 'connectors/image/size/ImageSize';
import { showErrorMessage } from 'components/Helpers/Toasts';
import { useEffect } from 'react';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

export function getPromotedCategories(): CategoryItemType[] | undefined {
    const t = useTypedTranslationFunction();
    const [{ data, fetching, error }] = usePromotedCategoriesQueryApi();

    useEffect(() => {
        if (error === undefined) {
            return;
        }

        const parsedErrors = getUserFriendlyErrors(error, t);
        if (parsedErrors.applicationError === undefined) {
            return;
        }

        showErrorMessage(parsedErrors.applicationError);
    }, [fetching]);

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
