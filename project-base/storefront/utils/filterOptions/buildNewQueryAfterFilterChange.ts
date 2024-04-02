import { DEFAULT_SORT } from 'config/constants';
import { ProductOrderingModeEnum } from 'graphql/types';
import { UrlQueries, FilterQueries } from 'types/urlQueries';
import {
    PAGE_QUERY_PARAMETER_NAME,
    LOAD_MORE_QUERY_PARAMETER_NAME,
    FILTER_QUERY_PARAMETER_NAME,
    SORT_QUERY_PARAMETER_NAME,
} from 'utils/queryParamNames';

export const buildNewQueryAfterFilterChange = (
    currentQuery: UrlQueries,
    newFilter: FilterQueries,
    newSort: ProductOrderingModeEnum | undefined,
) => {
    const isWithFilterParams =
        !!newFilter &&
        (!!newFilter.onlyInStock ||
            !!(newFilter.minimalPrice ?? undefined) ||
            !!(newFilter.maximalPrice ?? null) ||
            !!newFilter.brands?.length ||
            !!newFilter.flags?.length ||
            !!newFilter.parameters?.length);

    const newQuery: UrlQueries = {
        ...currentQuery,
        [PAGE_QUERY_PARAMETER_NAME]: undefined,
        [LOAD_MORE_QUERY_PARAMETER_NAME]: undefined,
        [FILTER_QUERY_PARAMETER_NAME]: isWithFilterParams ? JSON.stringify(newFilter) : undefined,
    } as const;

    if (newSort && newSort !== DEFAULT_SORT) {
        newQuery[SORT_QUERY_PARAMETER_NAME] = newSort;
    }

    return newQuery;
};
