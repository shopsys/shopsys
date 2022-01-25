import { CategoryDetailFragmentApi, ListedCategoryFragmentApi } from 'graphql/generated';
import { CategoryDetailType, ListedCategoryType } from 'types/category';
import { getFirstImageSize } from 'connectors/image/Image';
import { mapListedProductConnectionType } from 'connectors/products/Products';

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
        image: getFirstImageSize(listedCategoryApiData.images),
    };
};
