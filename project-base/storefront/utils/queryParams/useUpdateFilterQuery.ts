import { pushQueries } from './pushQueries';
import { useCurrentFilterQuery } from './useCurrentFilterQuery';
import { SEO_SENSITIVE_FILTERS } from 'config/constants';
import { TypeProductOrderingModeEnum } from 'graphql/types';
import { useRouter } from 'next/router';
import { useSessionStore } from 'store/useSessionStore';
import { FilterOptionsUrlQueryType, FilterOptionsParameterUrlQueryType } from 'types/productFilter';
import { UrlQueries, FilterQueries } from 'types/urlQueries';
import { buildNewQueryAfterFilterChange } from 'utils/filterOptions/buildNewQueryAfterFilterChange';
import { getFilterWithoutEmpty } from 'utils/filterOptions/getFilterWithoutEmpty';
import { getQueryWithoutSlugTypeParameterFromParsedUrlQuery } from 'utils/parsing/getQueryWithoutSlugTypeParameterFromParsedUrlQuery';
import {
    getChangedDefaultFiltersAfterAvailabilityChange,
    getChangedDefaultFiltersAfterBrandChange,
    getChangedDefaultFiltersAfterFlagChange,
    getChangedDefaultFiltersAfterMaximumPriceChange,
    getChangedDefaultFiltersAfterMinimumPriceChange,
    getChangedDefaultFiltersAfterParameterChange,
    getChangedDefaultFiltersAfterPriceChange,
    getChangedDefaultFiltersAfterSliderParameterChange,
    useRedirectFromSeoCategory,
} from 'utils/seoCategories/queryParamsUtils';

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

export const useUpdateFilterQuery = () => {
    const router = useRouter();
    const query = getQueryWithoutSlugTypeParameterFromParsedUrlQuery(router.query) as UrlQueries;
    const currentFilter = useCurrentFilterQuery();
    const defaultProductFiltersMap = useSessionStore((s) => s.defaultProductFiltersMap);
    const originalCategorySlug = useSessionStore((s) => s.originalCategorySlug);
    const redirectFromSeoCategory = useRedirectFromSeoCategory();

    const updateFilterInStockQuery = (value: FilterOptionsUrlQueryType['onlyInStock']) => {
        if (SEO_SENSITIVE_FILTERS.AVAILABILITY && originalCategorySlug) {
            redirectFromSeoCategory(() =>
                pushQueryFilter(
                    getChangedDefaultFiltersAfterAvailabilityChange(defaultProductFiltersMap, currentFilter, !!value),
                    originalCategorySlug,
                    defaultProductFiltersMap.sort,
                ),
            );

            return;
        }

        pushQueryFilter({ ...currentFilter, onlyInStock: value || undefined });
    };

    const updateFilterPricesQuery = (values: {
        minimalPrice: FilterOptionsUrlQueryType['minimalPrice'];
        maximalPrice: FilterOptionsUrlQueryType['maximalPrice'];
    }) => {
        if (SEO_SENSITIVE_FILTERS.PRICE && originalCategorySlug) {
            redirectFromSeoCategory(() =>
                pushQueryFilter(
                    getChangedDefaultFiltersAfterPriceChange(
                        defaultProductFiltersMap,
                        currentFilter,
                        values.minimalPrice,
                        values.maximalPrice,
                    ),
                    originalCategorySlug,
                    defaultProductFiltersMap.sort,
                ),
            );

            return;
        }

        pushQueryFilter({ ...currentFilter, ...values });
    };

    const updateFilterPriceMaximumQuery = (newMaxPrice: FilterOptionsUrlQueryType['maximalPrice']) => {
        if (SEO_SENSITIVE_FILTERS.PRICE && originalCategorySlug) {
            redirectFromSeoCategory(() =>
                pushQueryFilter(
                    getChangedDefaultFiltersAfterMaximumPriceChange(
                        defaultProductFiltersMap,
                        currentFilter,
                        newMaxPrice,
                    ),
                    originalCategorySlug,
                    defaultProductFiltersMap.sort,
                ),
            );

            return;
        }

        pushQueryFilter({ ...currentFilter, maximalPrice: newMaxPrice });
    };

    const updateFilterPriceMinimumQuery = (newMinPrice: FilterOptionsUrlQueryType['minimalPrice']) => {
        if (SEO_SENSITIVE_FILTERS.PRICE && originalCategorySlug) {
            redirectFromSeoCategory(() =>
                pushQueryFilter(
                    getChangedDefaultFiltersAfterMinimumPriceChange(
                        defaultProductFiltersMap,
                        currentFilter,
                        newMinPrice,
                    ),
                    originalCategorySlug,
                    defaultProductFiltersMap.sort,
                ),
            );

            return;
        }

        pushQueryFilter({ ...currentFilter, minimalPrice: newMinPrice });
    };

    const updateFilterBrandsQuery = (selectedUuid: string) => {
        if (SEO_SENSITIVE_FILTERS.BRANDS && originalCategorySlug) {
            redirectFromSeoCategory(() =>
                pushQueryFilter(
                    getChangedDefaultFiltersAfterBrandChange(defaultProductFiltersMap, currentFilter, selectedUuid),
                    originalCategorySlug,
                    defaultProductFiltersMap.sort,
                ),
            );

            return;
        }

        pushQueryFilter({ ...currentFilter, brands: handleUpdateFilter(selectedUuid, currentFilter?.brands) });
    };

    const updateFilterFlagsQuery = (selectedUuid: string) => {
        if (SEO_SENSITIVE_FILTERS.FLAGS && originalCategorySlug) {
            redirectFromSeoCategory(() =>
                pushQueryFilter(
                    getChangedDefaultFiltersAfterFlagChange(defaultProductFiltersMap, currentFilter, selectedUuid),
                    originalCategorySlug,
                    defaultProductFiltersMap.sort,
                ),
            );

            return;
        }

        pushQueryFilter({ ...currentFilter, flags: handleUpdateFilter(selectedUuid, currentFilter?.flags) });
    };

    const updateFilterParametersQuery = (
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
                            currentFilter,
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
                            currentFilter,
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
                JSON.stringify(currentFilter?.parameters || []),
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
                    currentFilter?.parameters![updatedParamaterIndex].values,
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
            ...currentFilter,
            parameters,
        });
    };

    const resetAllFilterQueries = () => {
        if (originalCategorySlug) {
            redirectFromSeoCategory(() =>
                pushQueryFilter(undefined, originalCategorySlug, defaultProductFiltersMap.sort),
            );

            return;
        }

        pushQueryFilter(undefined, originalCategorySlug);
    };

    const pushQueryFilter = (
        newFilter?: FilterQueries,
        pathnameOverride?: string,
        sortOverride?: TypeProductOrderingModeEnum,
    ) => {
        const newQuery = buildNewQueryAfterFilterChange(query, getFilterWithoutEmpty(newFilter), sortOverride);

        pushQueries(router, newQuery, true, pathnameOverride);
    };

    return {
        updateFilterInStockQuery,
        updateFilterPricesQuery,
        updateFilterPriceMaximumQuery,
        updateFilterPriceMinimumQuery,
        updateFilterBrandsQuery,
        updateFilterFlagsQuery,
        updateFilterParametersQuery,
        resetAllFilterQueries,
    };
};
