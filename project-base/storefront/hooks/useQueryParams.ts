import { ProductOrderingModeEnumApi } from 'graphql/generated';
import { getQueryWithoutSlugTypeParameter } from 'helpers/filterOptions/getQueryWithoutAllParameter';
import {
    FILTER_QUERY_PARAMETER_NAME,
    PAGE_QUERY_PARAMETER_NAME,
    SEARCH_QUERY_PARAMETER_NAME,
    SORT_QUERY_PARAMETER_NAME,
} from 'helpers/queryParams/queryParamNames';
import { useRouter } from 'next/router';
import { FilterOptionsParameterUrlQueryType, FilterOptionsUrlQueryType } from 'types/productFilter';

export type FilterQueries = FilterOptionsUrlQueryType | undefined;

export type UrlQueries = {
    [FILTER_QUERY_PARAMETER_NAME]?: string;
    [SEARCH_QUERY_PARAMETER_NAME]?: string;
    [SORT_QUERY_PARAMETER_NAME]?: ProductOrderingModeEnumApi;
    [PAGE_QUERY_PARAMETER_NAME]?: string;
};

const handleUpdateFilter = (selectedUuid: string | undefined, items: string[] | undefined): string[] | undefined => {
    if (selectedUuid === undefined) {
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

    const currentPage = Number(query[PAGE_QUERY_PARAMETER_NAME] || 1);
    const sort = query[SORT_QUERY_PARAMETER_NAME];
    const filter = JSON.parse(query[FILTER_QUERY_PARAMETER_NAME] || '{}') as FilterOptionsUrlQueryType;
    const isWithFilter = !!Object.keys(filter).length;

    const updateSort = (sorting: ProductOrderingModeEnumApi | undefined) => {
        pushQuerySort(sorting);
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

    const updateFilterPriceMaximum = (value: FilterOptionsUrlQueryType['maximalPrice']) => {
        pushQueryFilter({ ...filter, maximalPrice: value });
    };

    const updateFilterPriceMinimum = (value: FilterOptionsUrlQueryType['minimalPrice']) => {
        pushQueryFilter({ ...filter, minimalPrice: value });
    };

    const updateFilterBrands = (selectedUuid: string | undefined) => {
        pushQueryFilter({ ...filter, brands: handleUpdateFilter(selectedUuid, filter.brands) });
    };

    const updateFilterFlags = (selectedUuid: string | undefined) => {
        pushQueryFilter({ ...filter, flags: handleUpdateFilter(selectedUuid, filter.flags) });
    };

    const updateFilterParameters = (
        parameterUuid: string | undefined,
        paramaterOptionUuid: string | undefined,
        minimalValue?: number,
        maximalValue?: number,
    ) => {
        const parameters: FilterOptionsParameterUrlQueryType[] | undefined = (() => {
            if (!parameterUuid) {
                return undefined;
            }

            // deep clone parameters
            const newParameters: FilterOptionsParameterUrlQueryType[] = JSON.parse(
                JSON.stringify(filter.parameters || []),
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
                    filter.parameters![updatedParamaterIndex].values,
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
        pushQueryFilter(undefined);
    };

    const pushQuerySort = (sorting?: ProductOrderingModeEnumApi) => {
        const newQuery: UrlQueries = {
            ...query,
            page: undefined,
            [SORT_QUERY_PARAMETER_NAME]: sorting,
        } as const;

        pushQueries(newQuery);
    };

    const pushQueryFilter = (newFilter?: FilterQueries) => {
        const isWithFilterParams =
            !!newFilter &&
            (!!newFilter.onlyInStock ||
                !!(newFilter.minimalPrice ?? undefined) ||
                !!(newFilter.maximalPrice ?? null) ||
                !!newFilter.brands?.length ||
                !!newFilter.flags?.length ||
                !!newFilter.parameters?.length);

        const newQuery: UrlQueries = {
            ...query,
            page: undefined,
            [FILTER_QUERY_PARAMETER_NAME]: isWithFilterParams ? JSON.stringify(newFilter) : undefined,
        } as const;

        pushQueries(newQuery);
    };

    const pushQueries = (query: UrlQueries) => {
        // remove queries which are not set or removed
        (Object.keys(query) as Array<keyof typeof query>).forEach((key) => {
            if (typeof query[key] === 'undefined') {
                delete query[key];
            }
        });

        router.replace(
            {
                pathname: router.asPath.split('?')[0],
                query,
            },
            undefined,
            { shallow: true },
        );
    };

    return {
        currentPage,
        sort,
        filter,
        isWithFilter,
        updateSort,
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
