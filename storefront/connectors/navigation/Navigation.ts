import {
    CategoriesByColumnFragmentApi,
    ColumnCategoriesFragmentApi,
    ImagesDefaultFragmentApi,
    NavigationQueryApi,
    NavigationSubCategoriesLinkFragmentApi,
    useNavigationQueryApi,
} from 'graphql/generated';
import { getUserFriendlyErrors } from 'connectors/lib/friendlyErrorMessageParser';
import { ImageType } from 'components/Basic/Image/types';
import { mapImageSizeApiData } from 'connectors/image/size/ImageSize';
import { showErrorMessage } from 'components/Helpers/Toasts';
import { useEffect } from 'react';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

export type NavigationSubCategory = {
    name: string;
    slug: string;
};

export type NavigationCategory = {
    name: string;
    slug: string;
    image: {
        url: string;
        width: number;
        height: number;
    };
    children: NavigationSubCategory[];
};

export type NavigationCategoriesColumn = {
    columnNumber: number;
    categories: NavigationCategory[];
};

export type NavigationItem = {
    name: string;
    link: string;
    categoriesByColumns: NavigationCategoriesColumn[];
};

export function getNavigationItems(): NavigationItem[] {
    const t = useTypedTranslationFunction();
    const [{ data, fetching, error }] = useNavigationQueryApi();

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

    if (data?.navigation !== undefined) {
        return mapNavigation(data.navigation);
    }
    return [];
}

function mapNavigation(data: NavigationQueryApi['navigation']): NavigationItem[] {
    const mappedNavigation = [];

    for (const navigationItem of data) {
        mappedNavigation.push({
            ...navigationItem,
            categoriesByColumns: mapNavigationCategoriesByColumns(navigationItem.categoriesByColumns),
        });
    }
    return mappedNavigation;
}

function mapNavigationCategoriesByColumns(
    categoriesByColumns: CategoriesByColumnFragmentApi['categoriesByColumns'],
): NavigationCategoriesColumn[] {
    const mappedCategoriesByColumns = [];
    for (const categoriesByColumn of categoriesByColumns) {
        mappedCategoriesByColumns.push({
            ...categoriesByColumn,
            categories: mapCategories(categoriesByColumn.categories),
        });
    }

    return mappedCategoriesByColumns;
}

const mapCategoryImageApiData = (apiData: ImagesDefaultFragmentApi['images']): ImageType | null => {
    if (apiData === undefined || apiData === null) {
        return null;
    }
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

const mapSubCategories = (apiData: NavigationSubCategoriesLinkFragmentApi['children']): NavigationSubCategory[] => {
    if (apiData === undefined || apiData === null) {
        return [];
    }

    return apiData.map((subCategory) => {
        return {
            name: subCategory.name !== undefined && subCategory.name !== null ? subCategory.name : '',
            slug: subCategory.slug,
        };
    });
};

const mapCategories = (data: ColumnCategoriesFragmentApi['categories']): NavigationCategory[] => {
    const mappedCategories = [];
    for (const category of data) {
        if (category.images.length === 0 || category.images[0] === undefined || category.images[0] === null) {
            continue;
        }
        const mappedImages = mapCategoryImageApiData(category.images);
        if (mappedImages === null) {
            continue;
        }

        mappedCategories.push({
            name: category.name !== undefined && category.name !== null ? category.name : '',
            slug: category.slug,
            children: mapSubCategories(category.children),
            image: mappedImages,
        });
    }
    return mappedCategories;
};
