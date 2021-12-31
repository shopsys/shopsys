import { ProductOrderingModeEnumApi, SearchQueryApi, useSearchQueryApi } from 'graphql/generated';
import { useEffect, useState } from 'react';
import { FilterOptionsStateType } from 'types/productFilter';
import { ListedBrandType } from 'types/brand';
import { ListedCategoryType } from 'types/category';
import { ListedProductType } from 'types/product';
import { mapListedBrand } from 'connectors/brands/Brands';
import { mapListedCategoryApiData } from 'connectors/categories/Categories';
import { mapListedProductType } from 'connectors/products/Products';
import { mapParametersFilter } from 'helpers/filterOptions/MapParametersFilter';
import { mapProductFilterOptions } from 'helpers/filterOptions/MapProductFilterOptions';
import { mapSimpleArticleInterface } from 'connectors/articleInterface/ArticleInterface';
import { PaginationType } from 'redux/slices/user';
import { SearchType } from 'types/search';
import { SimpleArticleInterfaceType } from 'types/articleInterface';
import { useQueryError } from 'hooks/graphQl/UseQueryError';
import { useShopsysSelector } from 'redux/main';

export const getSearch = (
    searchQuery: string,
    searchProductsSort: ProductOrderingModeEnumApi,
    searchProductsPagination: PaginationType['paginationCursor'],
    optionsFilter: FilterOptionsStateType,
): SearchType | undefined => {
    const [result] = useSearchQueryApi({
        variables: {
            search: searchQuery,
            orderingMode: searchProductsSort,
            after: searchProductsPagination,
            filter: mapParametersFilter(optionsFilter),
        },
    });
    const { currencyCode } = useShopsysSelector((state) => state.domain);
    const [mappedResult, setMappedResult] = useState<SearchType | undefined>(
        mapSearchResult(result.data, currencyCode),
    );

    useQueryError(result.error);
    useEffect(() => {
        if (result.stale === false) {
            setMappedResult(mapSearchResult(result.data, currencyCode));
        }
    }, [result.data]);

    if (typeof window === 'undefined' && result.data !== undefined) {
        return mapSearchResult(result.data, currencyCode);
    }

    return mappedResult;
};

const mapSearchResult = (apiData: SearchQueryApi | undefined, currencyCode: string): SearchType | undefined => {
    if (apiData === undefined) {
        return undefined;
    }

    return {
        articlesSearch: mapArticlesSearchResults(apiData.articlesSearch),
        brandSearch: mapBrandSearchResults(apiData.brandSearch),
        categoriesSearch: {
            totalCount: apiData.categoriesSearch?.totalCount === undefined ? 0 : apiData.categoriesSearch.totalCount,
            categories: mapCategoriesSearchResults(apiData.categoriesSearch),
        },
        productsSearch: {
            totalCount: apiData.productsSearch?.totalCount === undefined ? 0 : apiData.productsSearch.totalCount,
            productFilterOptions:
                apiData.productsSearch !== undefined && apiData.productsSearch !== null
                    ? mapProductFilterOptions(apiData.productsSearch.productFilterOptions, currencyCode)
                    : null,
            products: mapProductsSearchResults(apiData.productsSearch, currencyCode),
        },
    };
};

const mapProductsSearchResults = (
    apiData: SearchQueryApi['productsSearch'],
    currencyCode: string,
): ListedProductType[] => {
    const mappedProducts = [];

    if (apiData?.edges !== undefined && apiData.edges !== null) {
        for (const productEdge of apiData.edges) {
            if (productEdge?.node !== undefined && productEdge.node !== null) {
                mappedProducts.push(mapListedProductType(productEdge.node, currencyCode));
            }
        }
    }

    return mappedProducts;
};

const mapCategoriesSearchResults = (apiData: SearchQueryApi['categoriesSearch']): ListedCategoryType[] => {
    const mappedCategories = [];

    if (apiData?.edges !== undefined && apiData.edges !== null) {
        for (const categoryEdge of apiData.edges) {
            if (categoryEdge?.node !== undefined && categoryEdge.node !== null) {
                mappedCategories.push(mapListedCategoryApiData(categoryEdge.node));
            }
        }
    }

    return mappedCategories;
};

export const mapBrandSearchResults = (apiData: SearchQueryApi['brandSearch']): ListedBrandType[] => {
    return apiData.map((brand) => mapListedBrand(brand));
};

export const mapArticlesSearchResults = (apiData: SearchQueryApi['articlesSearch']): SimpleArticleInterfaceType[] => {
    const mappedArticles = [];

    if (apiData === null) {
        return [];
    }

    for (const article of apiData) {
        if (article === null) {
            continue;
        }
        const mappedArticle = mapSimpleArticleInterface(article);
        if (mappedArticle !== undefined) {
            mappedArticles.push(mappedArticle);
        }
    }

    return mappedArticles;
};
