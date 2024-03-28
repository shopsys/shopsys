import { pushQueries } from './pushQueries';
import { useCurrentFilter } from './useCurrentFilter';
import { SEO_SENSITIVE_FILTERS, DEFAULT_SORT } from 'config/constants';
import { TypeProductOrderingModeEnum } from 'graphql/types';
import { buildNewQueryAfterFilterChange } from 'helpers/filterOptions/buildNewQueryAfterFilterChange';
import { getFilterWithoutEmpty } from 'helpers/filterOptions/getFilterWithoutEmpty';
import { getQueryWithoutSlugTypeParameterFromParsedUrlQuery } from 'helpers/parsing/getQueryWithoutSlugTypeParameterFromParsedUrlQuery';
import {
    LOAD_MORE_QUERY_PARAMETER_NAME,
    PAGE_QUERY_PARAMETER_NAME,
    SORT_QUERY_PARAMETER_NAME,
} from 'helpers/queryParamNames';
import { useRedirectFromSeoCategory, getChangedDefaultFilters } from 'helpers/seoCategories/queryParamsHelpers';
import { useRouter } from 'next/router';
import { useSessionStore } from 'store/useSessionStore';
import { UrlQueries } from 'types/urlQueries';

export const useUpdateSort = () => {
    const router = useRouter();
    const query = getQueryWithoutSlugTypeParameterFromParsedUrlQuery(router.query) as UrlQueries;
    const currentFilter = useCurrentFilter();
    const defaultProductFiltersMap = useSessionStore((s) => s.defaultProductFiltersMap);
    const originalCategorySlug = useSessionStore((s) => s.originalCategorySlug);
    const redirectFromSeoCategory = useRedirectFromSeoCategory();

    const updateSort = (sorting: TypeProductOrderingModeEnum) => {
        if (SEO_SENSITIVE_FILTERS.SORT && originalCategorySlug) {
            redirectFromSeoCategory(() => {
                const newQuery = buildNewQueryAfterFilterChange(
                    query,
                    getFilterWithoutEmpty(getChangedDefaultFilters(defaultProductFiltersMap, currentFilter)),
                    sorting,
                );

                pushQueries(router, newQuery, true, originalCategorySlug);
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

        pushQueries(router, newQuery, true);
    };

    return updateSort;
};
