import { mapListedProductConnectionPreviewType } from 'connectors/products/Products';
import { CategoryDetailFragmentApi } from 'graphql/generated';
import { CategoryDetailType } from 'types/category';

export const mapCategoryDetailData = (
    apiCategoryDetailData: CategoryDetailFragmentApi,
    currencyCode: string,
): CategoryDetailType => {
    return {
        ...apiCategoryDetailData,
        __typename: 'Category',
        productConnection: mapListedProductConnectionPreviewType(apiCategoryDetailData.products, currencyCode),
    };
};
