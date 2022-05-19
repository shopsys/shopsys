import {
    AUTOCOMPLETE_CATEGORY_LIMIT,
    AUTOCOMPLETE_PRODUCT_LIMIT,
} from 'components/Layout/Header/AutocompleteSearch/Autocomplete/Autocomplete';
import { mapSimpleArticlesInterface } from 'connectors/articleInterface/ArticleInterface';
import { mapSimpleCategoryConnectionApiData } from 'connectors/categories/Categories';
import { mapListedProductConnectionType } from 'connectors/products/Products';
import { AutocompleteSearchQueryApi, useAutocompleteSearchQueryApi } from 'graphql/generated';
import { useQueryError } from 'hooks/graphQl/UseQueryError';
import { useMemo } from 'react';
import { useShopsysSelector } from 'redux/main';
import { AutocompleteSearchType } from 'types/search';

export const MINIMAL_SEARCH_QUERY_LENGTH = 3 as const;

export const useAutocompleteSearch = (autocompleteSearch: string): AutocompleteSearchType | undefined => {
    const [result] = useAutocompleteSearchQueryApi({
        variables: {
            search: autocompleteSearch,
            maxCategoryCount: AUTOCOMPLETE_CATEGORY_LIMIT,
            maxProductCount: AUTOCOMPLETE_PRODUCT_LIMIT,
        },
        pause: autocompleteSearch.length < MINIMAL_SEARCH_QUERY_LENGTH,
        requestPolicy: 'network-only',
    });
    const { currencyCode } = useShopsysSelector((state) => state.domain);

    useQueryError(result.error);

    return useMemo(
        () =>
            autocompleteSearch.length < MINIMAL_SEARCH_QUERY_LENGTH || !result.data
                ? undefined
                : mapSearchResult(result.data, currencyCode),
        [autocompleteSearch.length, currencyCode, result.data],
    );
};

const mapSearchResult = (apiData: AutocompleteSearchQueryApi, currencyCode: string): AutocompleteSearchType => {
    return {
        ...apiData,
        articlesSearch: mapSimpleArticlesInterface(apiData.articlesSearch),
        categoriesSearch: mapSimpleCategoryConnectionApiData(apiData.categoriesSearch),
        productsSearch: mapListedProductConnectionType(apiData.productsSearch, currencyCode),
    };
};
