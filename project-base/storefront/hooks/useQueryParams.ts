import { DEFAULT_SORT, SEO_SENSITIVE_FILTERS } from 'config/constants';
import { ProductOrderingModeEnum } from 'graphql/types';
import { buildNewQueryAfterFilterChange } from 'helpers/filterOptions/buildNewQueryAfterFilterChange';
import { getFilterWithoutEmpty } from 'helpers/filterOptions/getFilterWithoutEmpty';
import {
    getChangedDefaultFilters,
    getChangedDefaultFiltersAfterAvailabilityChange,
    getChangedDefaultFiltersAfterBrandChange,
    getChangedDefaultFiltersAfterFlagChange,
    getChangedDefaultFiltersAfterMaximumPriceChange,
    getChangedDefaultFiltersAfterMinimumPriceChange,
    getChangedDefaultFiltersAfterParameterChange,
    getChangedDefaultFiltersAfterPriceChange,
    getChangedDefaultFiltersAfterSliderParameterChange,
    useRedirectFromSeoCategory,
} from 'helpers/filterOptions/seoCategories';
import {
    getQueryWithoutSlugTypeParameterFromParsedUrlQuery,
    getUrlQueriesWithoutFalsyValues,
    getUrlQueriesWithoutDynamicPageQueries,
} from 'helpers/parsing/urlParsing';
import { getDynamicPageQueryKey } from 'helpers/parsing/urlParsing';
import {
    FILTER_QUERY_PARAMETER_NAME,
    LOAD_MORE_QUERY_PARAMETER_NAME,
    PAGE_QUERY_PARAMETER_NAME,
    SEARCH_QUERY_PARAMETER_NAME,
    SORT_QUERY_PARAMETER_NAME,
} from 'helpers/queryParamNames';
import { useRouter } from 'next/router';
import { useSessionStore } from 'store/useSessionStore';
import { FilterOptionsParameterUrlQueryType, FilterOptionsUrlQueryType } from 'types/productFilter';

export type FilterQueries = FilterOptionsUrlQueryType | undefined;

export type UrlQueries = {
    [FILTER_QUERY_PARAMETER_NAME]?: string;
    [SEARCH_QUERY_PARAMETER_NAME]?: string;
    [SORT_QUERY_PARAMETER_NAME]?: ProductOrderingModeEnum;
    [PAGE_QUERY_PARAMETER_NAME]?: string;
    [LOAD_MORE_QUERY_PARAMETER_NAME]?: string;
};

const handleUpdateFilter = (selectedUuid: string | undefined, items: string[] | undefined): string[] | undefined => {
    if (!selectedUuid) {
        return undefined;
    }

    const newItems = [...(items || [])];
    const checkedIndex = items?.findIndex((b) => b === selectedUuid!);

    if (checkedIndex !== undefined && checkedIndex > -1) {
        newItems.splice(checkedIndex, 1);

        return newItems.length ? newItems : undefined;
    }

    newItems.push(selectedUuid);

    return newItems;
};

export const useQueryParams = () => {
    const router = useRouter();
    const query = getQueryWithoutSlugTypeParameterFromParsedUrlQuery(router.query) as UrlQueries;
    const defaultProductFiltersMap = useSessionStore((s) => s.defaultProductFiltersMap);
    const originalCategorySlug = useSessionStore((s) => s.originalCategorySlug);
    const redirectFromSeoCategory = useRedirectFromSeoCategory();

    const currentPage = Number(query[PAGE_QUERY_PARAMETER_NAME] || 1);
    const currentLoadMore = Number(query[LOAD_MORE_QUERY_PARAMETER_NAME] || 0);
    const searchString = query[SEARCH_QUERY_PARAMETER_NAME];
    const sort = query[SORT_QUERY_PARAMETER_NAME] ?? null;
    const filterQuery = query[FILTER_QUERY_PARAMETER_NAME];
    const filter = filterQuery ? (JSON.parse(filterQuery) as FilterOptionsUrlQueryType) : null;

    const updateSort = (sorting: ProductOrderingModeEnum) => {
        if (SEO_SENSITIVE_FILTERS.SORT && originalCategorySlug) {
            redirectFromSeoCategory(() =>
                pushQueryFilter(
                    getChangedDefaultFilters(defaultProductFiltersMap, filter),
                    originalCategorySlug,
                    sorting,
                ),
            );

            return;
        }

        pushQuerySort(sorting);
    };

    const updatePagination = (page: number) => {
        pushQueryPage(page);
    };

    const loadMore = () => {
        const updatedLoadMore = currentLoadMore + 1;
        const newQuery: UrlQueries = {
            ...query,
            [LOAD_MORE_QUERY_PARAMETER_NAME]: updatedLoadMore > 0 ? updatedLoadMore.toString() : undefined,
        } as const;

        pushQueries(newQuery, true);
    };

    const updateFilterInStock = (value: FilterOptionsUrlQueryType['onlyInStock']) => {
        if (SEO_SENSITIVE_FILTERS.AVAILABILITY && originalCategorySlug) {
            redirectFromSeoCategory(() =>
                pushQueryFilter(
                    getChangedDefaultFiltersAfterAvailabilityChange(defaultProductFiltersMap, filter, !!value),
                    originalCategorySlug,
                    defaultProductFiltersMap.sort,
                ),
            );

            return;
        }

        pushQueryFilter({ ...filter, onlyInStock: value || undefined });
    };

    const updateFilterPrices = (values: {
        minimalPrice: FilterOptionsUrlQueryType['minimalPrice'];
        maximalPrice: FilterOptionsUrlQueryType['maximalPrice'];
    }) => {
        if (SEO_SENSITIVE_FILTERS.PRICE && originalCategorySlug) {
            redirectFromSeoCategory(() =>
                pushQueryFilter(
                    getChangedDefaultFiltersAfterPriceChange(
                        defaultProductFiltersMap,
                        filter,
                        values.minimalPrice,
                        values.maximalPrice,
                    ),
                    originalCategorySlug,
                    defaultProductFiltersMap.sort,
                ),
            );

            return;
        }

        pushQueryFilter({ ...filter, ...values });
    };

    const updateFilterPriceMaximum = (newMaxPrice: FilterOptionsUrlQueryType['maximalPrice']) => {
        if (SEO_SENSITIVE_FILTERS.PRICE && originalCategorySlug) {
            redirectFromSeoCategory(() =>
                pushQueryFilter(
                    getChangedDefaultFiltersAfterMaximumPriceChange(defaultProductFiltersMap, filter, newMaxPrice),
                    originalCategorySlug,
                    defaultProductFiltersMap.sort,
                ),
            );

            return;
        }

        pushQueryFilter({ ...filter, maximalPrice: newMaxPrice });
    };

    const updateFilterPriceMinimum = (newMinPrice: FilterOptionsUrlQueryType['minimalPrice']) => {
        if (SEO_SENSITIVE_FILTERS.PRICE && originalCategorySlug) {
            redirectFromSeoCategory(() =>
                pushQueryFilter(
                    getChangedDefaultFiltersAfterMinimumPriceChange(defaultProductFiltersMap, filter, newMinPrice),
                    originalCategorySlug,
                    defaultProductFiltersMap.sort,
                ),
            );

            return;
        }

        pushQueryFilter({ ...filter, minimalPrice: newMinPrice });
    };

    const updateFilterBrands = (selectedUuid: string) => {
        if (SEO_SENSITIVE_FILTERS.BRANDS && originalCategorySlug) {
            redirectFromSeoCategory(() =>
                pushQueryFilter(
                    getChangedDefaultFiltersAfterBrandChange(defaultProductFiltersMap, filter, selectedUuid),
                    originalCategorySlug,
                    defaultProductFiltersMap.sort,
                ),
            );

            return;
        }

        pushQueryFilter({ ...filter, brands: handleUpdateFilter(selectedUuid, filter?.brands) });
    };

    const updateFilterFlags = (selectedUuid: string) => {
        if (SEO_SENSITIVE_FILTERS.FLAGS && originalCategorySlug) {
            redirectFromSeoCategory(() =>
                pushQueryFilter(
                    getChangedDefaultFiltersAfterFlagChange(defaultProductFiltersMap, filter, selectedUuid),
                    originalCategorySlug,
                    defaultProductFiltersMap.sort,
                ),
            );

            return;
        }

        pushQueryFilter({ ...filter, flags: handleUpdateFilter(selectedUuid, filter?.flags) });
    };

    const updateFilterParameters = (
        parameterUuid: string,
        paramaterOptionUuid: string | undefined,
        minimalValue?: number,
        maximalValue?: number,
    ) => {
        if (
            (SEO_SENSITIVE_FILTERS.PARAMETERS.CHECKBOX || SEO_SENSITIVE_FILTERS.PARAMETERS.SLIDER) &&
            originalCategorySlug
        ) {
            if (SEO_SENSITIVE_FILTERS.PARAMETERS.SLIDER && !paramaterOptionUuid) {
                redirectFromSeoCategory(() =>
                    pushQueryFilter(
                        getChangedDefaultFiltersAfterSliderParameterChange(
                            defaultProductFiltersMap,
                            filter,
                            parameterUuid,
                            minimalValue,
                            maximalValue,
                        ),
                        originalCategorySlug,
                        defaultProductFiltersMap.sort,
                    ),
                );

                return;
            }
            if (SEO_SENSITIVE_FILTERS.PARAMETERS.CHECKBOX && paramaterOptionUuid) {
                redirectFromSeoCategory(() =>
                    pushQueryFilter(
                        getChangedDefaultFiltersAfterParameterChange(
                            defaultProductFiltersMap,
                            filter,
                            parameterUuid,
                            paramaterOptionUuid,
                        ),
                        originalCategorySlug,
                        defaultProductFiltersMap.sort,
                    ),
                );

                return;
            }
        }

        const parameters: FilterOptionsParameterUrlQueryType[] | undefined = (() => {
            // deep clone parameters
            const newParameters: FilterOptionsParameterUrlQueryType[] = JSON.parse(
                JSON.stringify(filter?.parameters || []),
            );

            const updatedParamaterIndex = newParameters.findIndex(
                ({ parameter: newParameterId }) => newParameterId === parameterUuid,
            );

            const updatedParameterSharedProps: FilterOptionsParameterUrlQueryType = {
                parameter: parameterUuid,
                values: [],
                minimalValue,
                maximalValue,
            };

            if (updatedParamaterIndex !== -1) {
                const newValues = handleUpdateFilter(
                    paramaterOptionUuid,
                    filter?.parameters![updatedParamaterIndex].values,
                );

                newParameters[updatedParamaterIndex] = {
                    ...updatedParameterSharedProps,
                    values: newValues,
                };
            } else {
                newParameters.push({
                    ...updatedParameterSharedProps,
                    values: paramaterOptionUuid ? [paramaterOptionUuid] : undefined,
                });
            }

            const filteredParameters = newParameters.filter(
                (newParameter) =>
                    !!newParameter.values ||
                    typeof newParameter.maximalValue === 'number' ||
                    typeof newParameter.minimalValue === 'number',
            );

            return filteredParameters;
        })();

        pushQueryFilter({
            ...filter,
            parameters,
        });
    };

    const resetAllFilters = () => {
        if (originalCategorySlug) {
            redirectFromSeoCategory(() =>
                pushQueryFilter(undefined, originalCategorySlug, defaultProductFiltersMap.sort),
            );

            return;
        }

        pushQueryFilter(undefined, originalCategorySlug);
    };

    const pushQuerySort = (sorting: ProductOrderingModeEnum) => {
        const newQuery: UrlQueries = {
            ...query,
            [LOAD_MORE_QUERY_PARAMETER_NAME]: undefined,
            [PAGE_QUERY_PARAMETER_NAME]: undefined,
            [SORT_QUERY_PARAMETER_NAME]: sorting !== DEFAULT_SORT ? sorting : undefined,
        } as const;

        pushQueries(newQuery, true);
    };

    const pushQueryPage = (page: number) => {
        const newQuery: UrlQueries = {
            ...query,
            [LOAD_MORE_QUERY_PARAMETER_NAME]: undefined,
            [PAGE_QUERY_PARAMETER_NAME]: page > 1 ? page.toString() : undefined,
        } as const;

        pushQueries(newQuery, true);
    };

    const pushQueryFilter = (
        newFilter?: FilterQueries,
        pathnameOverride?: string,
        sortOverride?: ProductOrderingModeEnum,
    ) => {
        const newQuery = buildNewQueryAfterFilterChange(query, getFilterWithoutEmpty(newFilter), sortOverride);

        pushQueries(newQuery, true, pathnameOverride);
    };

    const pushQueries = (queries: UrlQueries, isPush?: boolean, pathnameOverride?: string) => {
        // remove queries which are not set or removed
        const filteredQueries = getUrlQueriesWithoutDynamicPageQueries(getUrlQueriesWithoutFalsyValues(queries));

        const asPathname = router.asPath.split('?')[0];
        const dynamicPageQueryKey = getDynamicPageQueryKey(router.pathname);

        let filteredQueriesWithDynamicParam = filteredQueries;
        if (dynamicPageQueryKey) {
            filteredQueriesWithDynamicParam = {
                [dynamicPageQueryKey]: pathnameOverride || asPathname,
                ...filteredQueries,
            };
        }

        router[isPush ? 'push' : 'replace'](
            {
                pathname: router.pathname,
                query: filteredQueriesWithDynamicParam,
            },
            {
                pathname: pathnameOverride || asPathname,
                query: filteredQueries,
            },
            { shallow: true },
        );
    };

    return {
        currentPage,
        currentLoadMore,
        searchString,
        sort,
        filter,
        updateSort,
        updatePagination,
        loadMore,
        updateFilterInStock,
        updateFilterPrices,
        updateFilterPriceMaximum,
        updateFilterPriceMinimum,
        updateFilterBrands,
        updateFilterFlags,
        updateFilterParameters,
        resetAllFilters,
    };
};
