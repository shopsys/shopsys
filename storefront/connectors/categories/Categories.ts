import { CategoryDetailFragmentApi, ListedCategoryFragmentApi } from 'graphql/generated';
import { CategoryDetailType } from 'types/category';
import { getFirstImageSize } from 'connectors/image/Image';
import { ListedCategoryType } from 'types/category';
import { ListedProductEdgesType } from 'types/product';
import { mapListedProductType } from 'connectors/products/Products';
import { mapPageInfoApiData } from 'connectors/pageInfo/PageInfo';
import { mapProductFilterOptions } from 'helpers/filterOptions/MapProductFilterOptions';

export const mapCategoryDetailData = (
    apiCategoryDetailData: CategoryDetailFragmentApi,
    currencyCode: string,
): CategoryDetailType => {
    const products: ListedProductEdgesType = {
        ...apiCategoryDetailData.products,
        totalCount:
            apiCategoryDetailData.products?.totalCount !== undefined ? apiCategoryDetailData.products.totalCount : 0,
        pageInfo: mapPageInfoApiData(apiCategoryDetailData.products?.pageInfo),
        edges: [],
        productFilterOptions:
            apiCategoryDetailData.products !== null
                ? mapProductFilterOptions(apiCategoryDetailData.products.productFilterOptions, currencyCode)
                : null,
    };

    if (apiCategoryDetailData.products?.edges !== undefined && apiCategoryDetailData.products.edges !== null) {
        for (const edge of apiCategoryDetailData.products.edges) {
            if (edge?.node === undefined || edge.node === null) {
                continue;
            }
            products.edges.push({
                ...edge,
                node: mapListedProductType(edge.node, currencyCode),
            });
        }
    }

    return {
        ...apiCategoryDetailData,
        __typename: 'Category',
        products: products,
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
