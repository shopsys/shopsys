import { CategoryDetailFragmentApi, ListedCategoryFragmentApi, ProductFilterApi } from 'graphql/generated';
import { CategoryDetailType } from 'types/category';
import { FilterOptionsParameterTypeEnum } from 'types/productFilter';
import { FilterOptionsStateType } from 'types/productFilter';
import { ListedCategoryType } from 'types/category';
import { ListedProductEdgesType } from 'types/product';
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
        productFilterOptions:
            apiCategoryDetailData.products !== undefined && apiCategoryDetailData.products !== null
                ? {
                      ...apiCategoryDetailData.products.productFilterOptions,
                      minimalPrice: parseFloat(apiCategoryDetailData.products.productFilterOptions.minimalPrice),
                      maximalPrice: parseFloat(apiCategoryDetailData.products.productFilterOptions.maximalPrice),
                      brands:
                          apiCategoryDetailData.products.productFilterOptions.brands !== null &&
                          apiCategoryDetailData.products.productFilterOptions.brands !== undefined
                              ? apiCategoryDetailData.products.productFilterOptions.brands
                              : [],
                      flags:
                          apiCategoryDetailData.products.productFilterOptions.flags !== null &&
                          apiCategoryDetailData.products.productFilterOptions.flags !== undefined
                              ? apiCategoryDetailData.products.productFilterOptions.flags
                              : [],
                      parameters: apiCategoryDetailData.products.productFilterOptions.parameters?.map((item) => ({
                          ...item,
                          type:
                              item.type === FilterOptionsParameterTypeEnum.ColorPicker
                                  ? FilterOptionsParameterTypeEnum.ColorPicker
                                  : FilterOptionsParameterTypeEnum.Checkbox,
                          values: item.values.map((value) => ({
                              ...value,
                              rgbHex: value.rgbHex !== undefined && value.rgbHex !== null ? value.rgbHex : undefined,
                          })),
                      })),
                      currencyCode,
                  }
                : null,
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

export const mapParametersFilter = (categoryParametersFilter: FilterOptionsStateType): ProductFilterApi => {
    return {
        ...categoryParametersFilter,
        minimalPrice: categoryParametersFilter.minimalPrice?.toString(),
        maximalPrice: categoryParametersFilter.maximalPrice?.toString(),
    };
};
