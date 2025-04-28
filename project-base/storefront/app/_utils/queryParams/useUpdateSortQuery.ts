import { useCurrentFilterQuery } from './useCurrentFilterQuery';
import { usePushQueries } from './usePushQueries';
import { DEFAULT_SORT, SEO_SENSITIVE_FILTERS } from 'config/constants';
import { TypeProductOrderingModeEnum } from 'graphql/types';
import { useSearchParams } from 'next/navigation';
import { useSessionStore } from 'store/useSessionStore';
import { UrlQueries } from 'types/urlQueries';
import { buildNewQueryAfterFilterChange } from 'utils/filterOptions/buildNewQueryAfterFilterChange';
import { getFilterWithoutEmpty } from 'utils/filterOptions/getFilterWithoutEmpty';
import { getQueryWithoutSlugTypeParameterFromParsedUrlQuery } from 'utils/parsing/getQueryWithoutSlugTypeParameterFromParsedUrlQuery';
import {
    LOAD_MORE_QUERY_PARAMETER_NAME,
    PAGE_QUERY_PARAMETER_NAME,
    SORT_QUERY_PARAMETER_NAME,
} from 'utils/queryParamNames';
import { getChangedDefaultFilters, useRedirectFromSeoCategory } from 'utils/seoCategories/queryParamsUtils';

export const useUpdateSortQuery = () => {
    const pushQueries = usePushQueries();
    const searchParams = useSearchParams();
    const query = getQueryWithoutSlugTypeParameterFromParsedUrlQuery(searchParams) as UrlQueries;
    const currentFilter = useCurrentFilterQuery();
    const defaultProductFiltersMap = useSessionStore((s) => s.defaultProductFiltersMap);
    const originalCategorySlug = useSessionStore((s) => s.originalCategorySlug);
    const redirectFromSeoCategory = useRedirectFromSeoCategory();

    const updateSortQuery = (sorting: TypeProductOrderingModeEnum) => {
        if (SEO_SENSITIVE_FILTERS.SORT && originalCategorySlug) {
            redirectFromSeoCategory(() => {
                const newQuery = buildNewQueryAfterFilterChange(
                    query,
                    getFilterWithoutEmpty(getChangedDefaultFilters(defaultProductFiltersMap, currentFilter)),
                    sorting,
                );
                pushQueries(newQuery);
            });

            return;
        }

        pushQuerySort(sorting);
    };

    const pushQuerySort = (sorting: TypeProductOrderingModeEnum) => {
        const newQuery: UrlQueries = {
            ...query,
            [LOAD_MORE_QUERY_PARAMETER_NAME]: undefined,
            [PAGE_QUERY_PARAMETER_NAME]: undefined,
            [SORT_QUERY_PARAMETER_NAME]: sorting !== DEFAULT_SORT ? sorting : undefined,
        } as const;

        pushQueries(newQuery);
    };

    return updateSortQuery;
};
