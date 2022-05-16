import { getFirstImage } from 'connectors/image/Image';
import { mapListedProductConnectionType } from 'connectors/products/Products';
import {
    CategoryDetailFragmentApi,
    ListedCategoryConnectionFragmentApi,
    ListedCategoryFragmentApi,
    SimpleCategoryConnectionFragmentApi,
    SimpleCategoryFragmentApi,
} from 'graphql/generated';
import {
    CategoryDetailType,
    ListedCategoryConnectionType,
    ListedCategoryType,
    SimpleCategoryConnectionType,
    SimpleCategoryType,
} from 'types/category';

export const mapCategoryDetailData = (
    apiCategoryDetailData: CategoryDetailFragmentApi,
    currencyCode: string,
): CategoryDetailType => {
    return {
        ...apiCategoryDetailData,
        __typename: 'Category',
        productConnection: mapListedProductConnectionType(apiCategoryDetailData.products, currencyCode),
        children: apiCategoryDetailData.children.map((child) => mapListedCategoryApiData(child)),
        linkedCategories: apiCategoryDetailData.linkedCategories.map((child) => mapListedCategoryApiData(child)),
    };
};

export const mapListedCategoryApiData = (listedCategoryApiData: ListedCategoryFragmentApi): ListedCategoryType => {
    return {
        ...listedCategoryApiData,
        image: getFirstImage(listedCategoryApiData.images),
    };
};

export const mapSimpleCategoryConnectionApiData = (
    apiData: SimpleCategoryConnectionFragmentApi,
): SimpleCategoryConnectionType => {
    const mappedCategories = [];

    if (apiData.edges !== null) {
        for (const categoryEdge of apiData.edges) {
            if (categoryEdge?.node !== undefined && categoryEdge.node !== null) {
                mappedCategories.push(mapSimpleCategoryApiData(categoryEdge.node));
            }
        }
    }
    return { totalCount: apiData.totalCount, categories: mappedCategories };
};

const mapSimpleCategoryApiData = (apiData: SimpleCategoryFragmentApi): SimpleCategoryType => {
    return apiData;
};

export const mapListedCategoryConnectionApiData = (
    apiData: ListedCategoryConnectionFragmentApi,
): ListedCategoryConnectionType => {
    const mappedCategories = [];

    if (apiData.edges !== null) {
        for (const categoryEdge of apiData.edges) {
            if (categoryEdge?.node !== undefined && categoryEdge.node !== null) {
                mappedCategories.push(mapListedCategoryApiData(categoryEdge.node));
            }
        }
    }

    return { totalCount: apiData.totalCount, categories: mappedCategories };
};

export const mapSimpleCategories = (apiData: SimpleCategoryFragmentApi[]): SimpleCategoryType[] => {
    return apiData.map((simpleCategoryApiData) => mapSimpleCategoryApiData(simpleCategoryApiData));
};
