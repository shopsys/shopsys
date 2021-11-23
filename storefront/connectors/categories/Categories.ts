import { CategoryDetailFragmentApi, ListedCategoryFragmentApi } from 'graphql/generated';
import { CategoryDetailType } from 'components/Pages/CategoryDetail/types';
import { ListedCategoryType } from './types';
import { ListedProductEdgesType } from 'components/Blocks/Product/types';
import { mapImageApiData } from 'connectors/image/Image';
import { mapListedProductType } from 'connectors/products/Products';
import { mapPageInfoApiData } from 'connectors/pageInfo/PageInfo';

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
    };

    if (apiCategoryDetailData?.products?.edges !== undefined && apiCategoryDetailData.products.edges !== null) {
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
        seoH1:
            apiCategoryDetailData.seoH1 !== undefined && apiCategoryDetailData.seoH1 !== null
                ? apiCategoryDetailData.seoH1
                : null,
        products: products,
        children: apiCategoryDetailData.children.map((child) => mapListedCategoryApiData(child)),
        linkedCategories: apiCategoryDetailData.linkedCategories.map((child) => mapListedCategoryApiData(child)),
    };
};

export const mapListedCategoryApiData = (listedCategoryApiData: ListedCategoryFragmentApi): ListedCategoryType => {
    return {
        ...listedCategoryApiData,
        image: mapImageApiData(listedCategoryApiData.images),
    };
};
