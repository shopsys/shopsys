import { mapSimpleArticlesInterface } from 'connectors/articleInterface/ArticleInterface';
import { mapListedBrandsApiData } from 'connectors/brands/Brands';
import { mapListedCategoryConnectionApiData } from 'connectors/categories/Categories';
import { mapListedProductConnectionType } from 'connectors/products/Products';
import { ProductOrderingModeEnumApi, SearchQueryApi, useSearchQueryApi } from 'graphql/generated';
import { mapParametersFilter } from 'helpers/filterOptions/MapParametersFilter';
import { isServer } from 'helpers/isServer';
import { useQueryError } from 'hooks/graphQl/UseQueryError';
import { useEffect, useState } from 'react';
import { useShopsysSelector } from 'redux/main';
import { initialState, PaginationType } from 'redux/slices/user';
import { FilterOptionsUrlQueryType } from 'types/productFilter';
import { SearchType } from 'types/search';

export const useSearch = (
    searchQuery: string,
    searchProductsSort: ProductOrderingModeEnumApi | null,
    searchProductsPagination: PaginationType['paginationCursor'],
    optionsFilter: FilterOptionsUrlQueryType,
): SearchType | undefined => {
    const [result] = useSearchQueryApi({
        variables: {
            search: searchQuery,
            orderingMode: searchProductsSort,
            after: searchProductsPagination,
            filter: mapParametersFilter(optionsFilter),
            first: initialState.pagination.pageSize,
        },
    });
    const { currencyCode } = useShopsysSelector((state) => state.domain);
    const [mappedResult, setMappedResult] = useState<SearchType | undefined>(
        mapSearchResult(result.data, currencyCode),
    );

    useQueryError(result.error);
    useEffect(() => {
        if (!result.stale) {
            setMappedResult(mapSearchResult(result.data, currencyCode));
        }
    }, [currencyCode, result.data, result.stale]);

    if (isServer() && result.data !== undefined) {
        return mapSearchResult(result.data, currencyCode);
    }

    return mappedResult;
};

const mapSearchResult = (apiData: SearchQueryApi | undefined, currencyCode: string): SearchType | undefined => {
    if (apiData === undefined) {
        return undefined;
    }

    return {
        articlesSearch: mapSimpleArticlesInterface(apiData.articlesSearch),
        brandSearch: mapListedBrandsApiData(apiData.brandSearch),
        categoriesSearch: mapListedCategoryConnectionApiData(apiData.categoriesSearch),
        productsSearch: mapListedProductConnectionType(apiData.productsSearch, currencyCode),
    };
};
