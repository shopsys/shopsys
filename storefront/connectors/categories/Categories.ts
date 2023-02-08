import { getFirstImage } from 'connectors/image/Image';
import { mapListedProductConnectionPreviewType } from 'connectors/products/Products';
import { CategoryDetailFragmentApi, ListedCategoryFragmentApi } from 'graphql/generated';
import { CategoryDetailType, ListedCategoryType } from 'types/category';

export const mapCategoryDetailData = (
    apiCategoryDetailData: CategoryDetailFragmentApi,
    currencyCode: string,
): CategoryDetailType => {
    return {
        ...apiCategoryDetailData,
        __typename: 'Category',
        productConnection: mapListedProductConnectionPreviewType(apiCategoryDetailData.products, currencyCode),
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
