import { DEFAULT_SORT, SEO_SENSITIVE_FILTERS } from 'config/constants';
import { TypeProductOrderingModeEnum } from 'graphql/types';
import { useRouter } from 'next/router';
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
import { pushQueries } from './pushQueries';
import { useCurrentFilterQuery } from './useCurrentFilterQuery';

type SortTarget = {
    query: UrlQueries;
    // set when the sort leaves an SEO category for its original category
    pathnameOverride?: string;
};

/**
 * Where a sort change leads, for the navigation itself and for the native sort links opened in a new tab.
 */
export const useGetSortTarget = () => {
    const router = useRouter();
    const query = getQueryWithoutSlugTypeParameterFromParsedUrlQuery(router.query) as UrlQueries;
    const currentFilter = useCurrentFilterQuery();
    const defaultProductFiltersMap = useSessionStore((s) => s.defaultProductFiltersMap);
    const originalCategorySlug = useSessionStore((s) => s.originalCategorySlug);

    const getSortTarget = (sorting: TypeProductOrderingModeEnum): SortTarget => {
        if (SEO_SENSITIVE_FILTERS.SORT && originalCategorySlug) {
            return {
                query: buildNewQueryAfterFilterChange(
                    query,
                    getFilterWithoutEmpty(getChangedDefaultFilters(defaultProductFiltersMap, currentFilter)),
                    sorting,
                ),
                pathnameOverride: originalCategorySlug,
            };
        }

        return {
            query: {
                ...query,
                [LOAD_MORE_QUERY_PARAMETER_NAME]: undefined,
                [PAGE_QUERY_PARAMETER_NAME]: undefined,
                [SORT_QUERY_PARAMETER_NAME]: sorting !== DEFAULT_SORT ? sorting : undefined,
            },
        };
    };

    return getSortTarget;
};

export const useUpdateSortQuery = () => {
    const router = useRouter();
    const getSortTarget = useGetSortTarget();
    const redirectFromSeoCategory = useRedirectFromSeoCategory();

    const updateSortQuery = (sorting: TypeProductOrderingModeEnum) => {
        const { query: newQuery, pathnameOverride } = getSortTarget(sorting);

        if (pathnameOverride) {
            redirectFromSeoCategory(() => pushQueries(router, newQuery, true, pathnameOverride));

            return;
        }

        pushQueries(router, newQuery, true);
    };

    return updateSortQuery;
};
