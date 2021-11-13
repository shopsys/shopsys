import { EnrichedSearchQueryApi, ProductOrderingModeEnumApi, useEnrichedSearchQueryApi } from 'graphql/generated';
import { ListedArticleType, ListedBlogArticleType } from 'connectors/articles/types';
import { useEffect, useState } from 'react';
import { EnrichedSearchType } from './types';
import { ListedProductType } from 'connectors/products/types';
import { mapListedArticleApiData } from 'connectors/articles/Articles';
import { mapListedBrandApiData } from 'connectors/brands/Brands';
import { mapListedCategoryApiData } from 'connectors/categories/Categories';
import { mapListedProductType } from 'connectors/products/Products';
import { PaginationType } from 'redux/slices/user';
import { useQueryError } from 'hooks/graphQl/UseQueryError';
import { useShopsysSelector } from 'redux/main';

export const getEnrichedSearch = (
    searchQuery: string,
    searchProductsSort: ProductOrderingModeEnumApi,
    searchProductsPagination: PaginationType['paginationCursor'],
): EnrichedSearchType | undefined => {
    const [result] = useEnrichedSearchQueryApi({
        variables: { search: searchQuery, orderingMode: searchProductsSort, after: searchProductsPagination },
    });
    const { currencyCode } = useShopsysSelector((state) => state.domain);
    const [mappedResult, setMappedResult] = useState<EnrichedSearchType | undefined>(
        mapEnrichedSearchResult(result.data, currencyCode),
    );

    useQueryError(result.error);
    useEffect(() => {
        setMappedResult(mapEnrichedSearchResult(result.data, currencyCode));
    }, [result.data]);

    if (typeof window === 'undefined' && result.data !== undefined) {
        return mapEnrichedSearchResult(result.data, currencyCode);
    }

    return mappedResult;
};

const mapEnrichedSearchResult = (
    apiData: EnrichedSearchQueryApi | undefined,
    currencyCode: string,
): EnrichedSearchType | undefined => {
    if (apiData === undefined) {
        return undefined;
    }

    return {
        productsSearch: {
            totalCount: apiData.productsSearch?.totalCount === undefined ? 0 : apiData.productsSearch.totalCount,
            products: mapEnrichedProductsSearchResults(apiData.productsSearch, currencyCode),
        },
    };
};

const mapEnrichedProductsSearchResults = (
    apiData: EnrichedSearchQueryApi['productsSearch'],
    currencyCode: string,
): ListedProductType[] => {
    const mappedProducts = [];

    if (apiData?.edges !== undefined && apiData.edges !== null) {
        for (const productEdge of apiData.edges) {
            if (productEdge?.node !== undefined && productEdge?.node !== null) {
                mappedProducts.push({
                    ...productEdge.node,
                    flags: mapFlagsApiData(productEdge.node.flags),
                    isMainVariant: productEdge.node.__typename === 'MainVariant',
                    detailSlug: productEdge.node.slug,
                    availability: productEdge.node.availability.name,
                    name: productEdge.node.name,
                    price: mapProductPriceApiData(productEdge.node.price, currencyCode),
                    image: mapImageApiData(productEdge.node.images),
                });
            }
        }
    }

    return mappedProducts;
};
