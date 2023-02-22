import {
    AUTOCOMPLETE_CATEGORY_LIMIT,
    AUTOCOMPLETE_PRODUCT_LIMIT,
} from 'components/Layout/Header/AutocompleteSearch/Autocomplete';
import { mapConnectionEdges } from 'connectors/connection/Connection';
import { AutocompleteSearchQueryApi, useAutocompleteSearchQueryApi } from 'graphql/generated';
import { useQueryError } from 'hooks/graphQl/useQueryError';
import { useMemo } from 'react';
import { AutocompleteSearchType } from 'types/search';

export const MINIMAL_SEARCH_QUERY_LENGTH = 3 as const;

export const useAutocompleteSearch = (autocompleteSearch: string): [AutocompleteSearchType | undefined, boolean] => {
    const [result] = useAutocompleteSearchQueryApi({
        variables: {
            search: autocompleteSearch,
            maxCategoryCount: AUTOCOMPLETE_CATEGORY_LIMIT,
            maxProductCount: AUTOCOMPLETE_PRODUCT_LIMIT,
        },
        pause: autocompleteSearch.length < MINIMAL_SEARCH_QUERY_LENGTH,
        requestPolicy: 'network-only',
    });

    useQueryError(result.error);

    return useMemo(
        () => [
            autocompleteSearch.length < MINIMAL_SEARCH_QUERY_LENGTH || !result.data
                ? undefined
                : mapSearchResult(result.data),
            result.fetching,
        ],
        [autocompleteSearch.length, result.data, result.fetching],
    );
};

const mapSearchResult = (apiData: AutocompleteSearchQueryApi): AutocompleteSearchType => {
    return {
        ...apiData,
        categoriesSearch: {
            totalCount: apiData.categoriesSearch.totalCount,
            categories: mapConnectionEdges(apiData.categoriesSearch.edges),
        },
    };
};
