import {
    AUTOCOMPLETE_CATEGORY_LIMIT,
    AUTOCOMPLETE_PRODUCT_LIMIT,
} from 'components/Layout/Header/AutocompleteSearch/Autocomplete/Autocomplete';
import { AutocompleteSearchQueryApi, useAutocompleteSearchQueryApi } from 'graphql/generated';
import { useEffect, useState } from 'react';
import { AutocompleteSearchType } from 'types/search';
import { mapSimpleArticlesInterface } from 'connectors/articleInterface/ArticleInterface';
import { mapSimpleCategoryConnectionApiData } from 'connectors/categories/Categories';
import { mapSimpleProductConnectionApiData } from 'connectors/products/SimpleProduct';
import { useQueryError } from 'hooks/graphQl/UseQueryError';
import { useShopsysSelector } from 'redux/main';

export const useAutocompleteSearch = (autocompleteSearch: string): AutocompleteSearchType | undefined => {
    const [result] = useAutocompleteSearchQueryApi({
        variables: {
            search: autocompleteSearch,
            maxCategoryCount: AUTOCOMPLETE_CATEGORY_LIMIT,
            maxProductCount: AUTOCOMPLETE_PRODUCT_LIMIT,
        },
        pause: autocompleteSearch.length < 3,
        requestPolicy: 'network-only',
    });
    const [mappedResult, setMappedResult] = useState<AutocompleteSearchType | undefined>(undefined);
    const { currencyCode } = useShopsysSelector((state) => state.domain);

    useQueryError(result.error);
    useEffect(() => {
        if (result.data !== undefined) {
            setMappedResult(mapSearchResult(result.data, currencyCode));
        }
    }, [result.data]);
    useEffect(() => {
        if (autocompleteSearch.length < 3 && mappedResult !== undefined) {
            setMappedResult(undefined);
        }
    }, [autocompleteSearch]);

    return mappedResult;
};

const mapSearchResult = (apiData: AutocompleteSearchQueryApi, currencyCode: string): AutocompleteSearchType => {
    return {
        ...apiData,
        articlesSearch: mapSimpleArticlesInterface(apiData.articlesSearch),
        categoriesSearch: mapSimpleCategoryConnectionApiData(apiData.categoriesSearch),
        productsSearch: mapSimpleProductConnectionApiData(apiData.productsSearch, currencyCode),
    };
};
