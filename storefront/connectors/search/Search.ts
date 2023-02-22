import { DEFAULT_PAGE_SIZE } from 'components/Blocks/Pagination/Pagination';
import { mapConnectionEdges } from 'connectors/connection/Connection';
import { ProductOrderingModeEnumApi, SearchQueryApi, useSearchQueryApi } from 'graphql/generated';
import { mapParametersFilter } from 'helpers/filterOptions/mapParametersFilter';
import { isServer } from 'helpers/misc/isServer';
import { useQueryError } from 'hooks/graphQl/useQueryError';
import { useEffect, useState } from 'react';
import { useShopsysSelector } from 'redux/main';
import { FilterOptionsUrlQueryType } from 'types/productFilter';
import { SearchType } from 'types/search';

export const useSearch = (
    searchQuery: string,
    searchProductsSort: ProductOrderingModeEnumApi | null,
    optionsFilter: FilterOptionsUrlQueryType | null,
): SearchType | undefined => {
    const [result] = useSearchQueryApi({
        variables: {
            search: searchQuery,
            orderingMode: searchProductsSort,
            filter: mapParametersFilter(optionsFilter),
            pageSize: DEFAULT_PAGE_SIZE,
        },
    });
    const { currencyCode } = useShopsysSelector((state) => state.domain);
    const [mappedResult, setMappedResult] = useState<SearchType | undefined>(mapSearchResult(result.data));

    useQueryError(result.error);
    useEffect(() => {
        if (!result.stale) {
            setMappedResult(mapSearchResult(result.data));
        }
    }, [currencyCode, result.data, result.stale]);

    if (isServer() && result.data !== undefined) {
        return mapSearchResult(result.data);
    }

    return mappedResult;
};

const mapSearchResult = (apiData: SearchQueryApi | undefined): SearchType | undefined => {
    if (apiData === undefined) {
        return undefined;
    }

    return {
        ...apiData,
        categoriesSearch: {
            totalCount: apiData.categoriesSearch.totalCount,
            categories: mapConnectionEdges(apiData.categoriesSearch.edges),
        },
    };
};
