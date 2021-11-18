import { CategoryDetailFragmentApi, CategoryPreviewFragmentApi, PageInfoApi } from 'graphql/generated';
import { CategoryDetailType } from 'components/Pages/CategoryDetail/types';
import { ListedProductEdgesType } from 'components/Blocks/Product/types';
import { mapImageSizeApiData } from 'connectors/image/size/ImageSize';
import { mapListedProductNode } from 'connectors/products/Products';

const mapPageInfoApiData = (pageInfoApiData: PageInfoApi | undefined) => {
    return {
        startCursor:
            pageInfoApiData?.startCursor !== undefined && pageInfoApiData.startCursor !== null
                ? pageInfoApiData.startCursor
                : '',
        endCursor:
            pageInfoApiData?.endCursor !== undefined && pageInfoApiData.endCursor !== null
                ? pageInfoApiData.endCursor
                : '',
        hasNextPage:
            pageInfoApiData?.hasNextPage !== undefined && pageInfoApiData.hasNextPage !== null
                ? pageInfoApiData.hasNextPage
                : false,
        hasPreviousPage:
            pageInfoApiData?.hasPreviousPage !== undefined && pageInfoApiData.hasPreviousPage !== null
                ? pageInfoApiData.hasPreviousPage
                : false,
    };
};

const mapCategoryPreviewApiData = (categoryApiData: CategoryPreviewFragmentApi) => {
    return {
        ...categoryApiData,
        products:
            categoryApiData?.products !== undefined && categoryApiData.products !== null
                ? categoryApiData.products
                : undefined,
        image:
            categoryApiData.images.length > 0 &&
            0 in categoryApiData.images &&
            categoryApiData.images[0].sizes !== undefined &&
            0 in categoryApiData.images[0].sizes
                ? mapImageSizeApiData(categoryApiData.images[0].sizes[0])
                : null,
    };
};

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
                node: mapListedProductNode(edge.node, currencyCode),
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
        children: apiCategoryDetailData.children.map((child) => mapCategoryPreviewApiData(child)),
        linkedCategories: apiCategoryDetailData.linkedCategories.map((child) => mapCategoryPreviewApiData(child)),
    };
};
