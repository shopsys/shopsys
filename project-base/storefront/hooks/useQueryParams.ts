import { ProductOrderingModeEnumApi } from 'graphql/generated';
import { getQueryWithoutSlugTypeParameter } from 'helpers/filterOptions/getQueryWithoutAllParameter';
import { getFilteredQueries } from 'helpers/queryParams/queryHandlers';
import {
    getChangedDefaultFilters,
    getChangedDefaultFiltersAfterFlagChange,
    getChangedDefaultFiltersAfterMaximumPriceChange,
    getChangedDefaultFiltersAfterMinimumPriceChange,
    getChangedDefaultFiltersAfterParameterChange,
    getChangedDefaultFiltersAfterSliderParameterChange,
} from 'helpers/filterOptions/seoCategories';
import {
    FILTER_QUERY_PARAMETER_NAME,
    PAGE_QUERY_PARAMETER_NAME,
    SEARCH_QUERY_PARAMETER_NAME,
    SORT_QUERY_PARAMETER_NAME,
} from 'helpers/queryParams/queryParamNames';
import { useRouter } from 'next/router';
import { useSessionStore } from 'store/zustand/useSessionStore';
import { FilterOptionsParameterUrlQueryType, FilterOptionsUrlQueryType } from 'types/productFilter';

export type FilterQueries = FilterOptionsUrlQueryType | undefined;

export type UrlQueries = {
    [FILTER_QUERY_PARAMETER_NAME]?: string;
    [SEARCH_QUERY_PARAMETER_NAME]?: string;
    [SORT_QUERY_PARAMETER_NAME]?: ProductOrderingModeEnumApi;
    [PAGE_QUERY_PARAMETER_NAME]?: string;
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
    const query = getQueryWithoutSlugTypeParameter(router.query) as unknown as UrlQueries;
    const defaultProductFiltersMap = useSessionStore((s) => s.defaultProductFiltersMap);
    const originalCategorySlug = useSessionStore((s) => s.originalCategorySlug);

    const currentPage = Number(query[PAGE_QUERY_PARAMETER_NAME] || 1);
    const searchString = query[SEARCH_QUERY_PARAMETER_NAME];
    const sort = query[SORT_QUERY_PARAMETER_NAME] ?? null;
    const filterQuery = query[FILTER_QUERY_PARAMETER_NAME];
    const filter = filterQuery ? (JSON.parse(filterQuery) as FilterOptionsUrlQueryType) : null;

    const updateSort = (sorting: ProductOrderingModeEnumApi) => {
        if (originalCategorySlug) {
            pushQueryFilter(getChangedDefaultFilters(defaultProductFiltersMap, filter), originalCategorySlug, sorting);

            return;
        }

        pushQuerySort(sorting);
    };

    const updatePagination = (page: number) => {
        pushQueryPage(page);
    };

    const updateFilterInStock = (value: FilterOptionsUrlQueryType['onlyInStock']) => {
        pushQueryFilter({ ...filter, onlyInStock: value || undefined });
    };

    const updateFilterPrices = (values: {
        maximalPrice: FilterOptionsUrlQueryType['maximalPrice'];
        minimalPrice: FilterOptionsUrlQueryType['minimalPrice'];
    }) => {
        pushQueryFilter({ ...filter, ...values });
    };

    const updateFilterPriceMaximum = (newMaxPrice: FilterOptionsUrlQueryType['maximalPrice']) => {
        if (originalCategorySlug) {
            pushQueryFilter(
                getChangedDefaultFiltersAfterMaximumPriceChange(defaultProductFiltersMap, filter, newMaxPrice),
                originalCategorySlug,
                defaultProductFiltersMap.sort,
            );

            return;
        }

        pushQueryFilter({ ...filter, maximalPrice: newMaxPrice });
    };

    const updateFilterPriceMinimum = (newMinPrice: FilterOptionsUrlQueryType['minimalPrice']) => {
        if (originalCategorySlug) {
            pushQueryFilter(
                getChangedDefaultFiltersAfterMinimumPriceChange(defaultProductFiltersMap, filter, newMinPrice),
                originalCategorySlug,
                defaultProductFiltersMap.sort,
            );

            return;
        }

        pushQueryFilter({ ...filter, minimalPrice: newMinPrice });
    };

    const updateFilterBrands = (selectedUuid: string) => {
        pushQueryFilter({ ...filter, brands: handleUpdateFilter(selectedUuid, filter?.brands) });
    };

    const updateFilterFlags = (selectedUuid: string) => {
        if (originalCategorySlug) {
            pushQueryFilter(
                getChangedDefaultFiltersAfterFlagChange(defaultProductFiltersMap, filter, selectedUuid),
                originalCategorySlug,
                defaultProductFiltersMap.sort,
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
        if (originalCategorySlug) {
            if (!paramaterOptionUuid) {
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
                );

                return;
            }

            pushQueryFilter(
                getChangedDefaultFiltersAfterParameterChange(
                    defaultProductFiltersMap,
                    filter,
                    parameterUuid,
                    paramaterOptionUuid,
                ),
                originalCategorySlug,
                defaultProductFiltersMap.sort,
            );

            return;
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
                    values: paramaterOptionUuid ? [paramaterOptionUuid] : [],
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
        pushQueryFilter(undefined, originalCategorySlug);
    };

    const pushQuerySort = (sorting?: ProductOrderingModeEnumApi) => {
        const newQuery: UrlQueries = {
            ...query,
            page: undefined,
            [SORT_QUERY_PARAMETER_NAME]: sorting,
        } as const;

        pushQueries(newQuery, true);
    };

    const pushQueryPage = (page?: number) => {
        const newQuery: UrlQueries = {
            ...query,
            [PAGE_QUERY_PARAMETER_NAME]: page !== undefined && page > 1 ? page.toString() : undefined,
        } as const;

        pushQueries(newQuery, true);
    };

    const pushQueryFilter = (
        newFilter?: FilterQueries,
        pathnameOverride?: string,
        sortOverride?: ProductOrderingModeEnumApi,
    ) => {
        const isWithFilterParams =
            !!newFilter &&
            (!!newFilter.onlyInStock ||
                !!(newFilter.minimalPrice ?? undefined) ||
                !!(newFilter.maximalPrice ?? null) ||
                !!newFilter.brands?.length ||
                !!newFilter.flags?.length ||
                !!newFilter.parameters?.length);

        if (newFilter) {
            (Object.keys(newFilter) as Array<keyof typeof newFilter>).forEach((key) => {
                const newFilterValue = newFilter[key];
                if (Array.isArray(newFilterValue) && newFilterValue.length === 0) {
                    delete newFilter[key];
                }
            });
        }

        const newQuery: UrlQueries = {
            ...query,
            page: undefined,
            [FILTER_QUERY_PARAMETER_NAME]: isWithFilterParams ? JSON.stringify(newFilter) : undefined,
        } as const;

        if (sortOverride) {
            newQuery[SORT_QUERY_PARAMETER_NAME] = sortOverride;
        }

        pushQueries(newQuery, true, pathnameOverride);
    };

    const pushQueries = (queries: UrlQueries, isPush?: boolean, pathnameOverride?: string) => {
        // remove queries which are not set or removed
        const filteredQueries = getFilteredQueries(queries);

        router[isPush ? 'push' : 'replace'](
            {
                pathname: pathnameOverride || router.asPath.split('?')[0],
                query: filteredQueries,
            },
            undefined,
            { shallow: true },
        );
    };

    return {
        currentPage,
        searchString,
        sort,
        filter,
        updateSort,
        updatePagination,
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
