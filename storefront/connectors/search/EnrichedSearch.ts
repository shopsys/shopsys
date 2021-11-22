import { EnrichedSearchQueryApi, ProductOrderingModeEnumApi, useEnrichedSearchQueryApi } from 'graphql/generated';
import { ListedArticleType, ListedBlogArticleType } from 'connectors/articles/types';
import { useEffect, useState } from 'react';
import { EnrichedSearchType } from './types';
import { ListedBrandType } from 'connectors/brands/types';
import { ListedCategoryType } from 'connectors/categories/types';
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
        articlesSearch: mapEnrichedArticlesSearchResults(apiData.articlesSearch),
        brandSearch: mapEnrichedBrandSearchResults(apiData.brandSearch),
        categoriesSearch: {
            totalCount: apiData.categoriesSearch?.totalCount === undefined ? 0 : apiData.categoriesSearch.totalCount,
            categories: mapEnrichedCategoriesSearchResults(apiData.categoriesSearch),
        },
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
                mappedProducts.push(mapListedProductType(productEdge.node, currencyCode));
            }
        }
    }

    return mappedProducts;
};

const mapEnrichedCategoriesSearchResults = (
    apiData: EnrichedSearchQueryApi['categoriesSearch'],
): ListedCategoryType[] => {
    const mappedCategories = [];

    if (apiData?.edges !== undefined && apiData.edges !== null) {
        for (const categoryEdge of apiData.edges) {
            if (categoryEdge?.node !== undefined && categoryEdge?.node !== null) {
                mappedCategories.push(mapListedCategoryApiData(categoryEdge.node));
            }
        }
    }

    return mappedCategories;
};

export const mapEnrichedBrandSearchResults = (apiData: EnrichedSearchQueryApi['brandSearch']): ListedBrandType[] => {
    return apiData.map((brand) => mapListedBrandApiData(brand));
};

export const mapEnrichedArticlesSearchResults = (
    apiData: EnrichedSearchQueryApi['articlesSearch'],
): (ListedArticleType | ListedBlogArticleType)[] => {
    const mappedArticles = [];

    if (apiData !== undefined && apiData !== null) {
        for (const article of apiData) {
            if (article === undefined || article === null) {
                continue;
            }
            const mappedArticle = mapListedArticleApiData(article);
            if (mappedArticle !== undefined) {
                mappedArticles.push(mappedArticle);
            }
        }
    }

    return mappedArticles;
};
