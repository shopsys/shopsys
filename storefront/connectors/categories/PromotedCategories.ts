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
            image:
                apiCategory?.images !== undefined && apiCategory.images.length > 0
                    ? mapCategoryImageApiData(apiCategory.images)
                    : null,
        };
    });
};

const mapCategoryImageApiData = (apiData: ImagesDefaultFragmentApi['images']): ImageType | null => {
    const categoryImageData = apiData[0];
    if (
        categoryImageData === undefined ||
        categoryImageData === null ||
        categoryImageData.sizes[0] === undefined ||
        categoryImageData.sizes[0] === null
    ) {
        return null;
    }

    return mapImageSizeApiData(categoryImageData.sizes[0]);
};

const mapImageSizeApiData = (apiData: ArrayElement<ImageSizesFragmentApi['sizes']>): ImageType|null => {
    const size = apiData;
    if (size.url === undefined
        || size.url === null
        || size.width === undefined
        || size.width === null
        || size.height === undefined
        || size.height === null
    ) {
        return null;
    }

    return {
        url: size.url,
        width: size.width,
        height: size.height,
    };
}
