import { DEFAULT_SORT, SEO_SENSITIVE_FILTERS } from 'config/constants';
import { ProductFilterOptionsFragment } from 'graphql/requests/productFilterOptions/fragments/ProductFilterOptionsFragment.generated';
import { ProductOrderingModeEnum } from 'graphql/types';
import { mergeNullableArrays } from 'helpers/arrayUtils';
import { DefaultProductFiltersMapType } from 'store/slices/createSeoCategorySlice';
import { useSessionStore } from 'store/useSessionStore';
import { FilterOptionsParameterUrlQueryType, FilterOptionsUrlQueryType } from 'types/productFilter';

export const getEmptyDefaultProductFiltersMap = (): DefaultProductFiltersMapType => ({
    flags: new Set(),
    brands: new Set(),
    sort: DEFAULT_SORT,
    parameters: new Map(),
});

export const getDefaultFilterFromFilterOptions = (
    productFilterOptions: ProductFilterOptionsFragment | undefined,
    defaultOrderingMode: ProductOrderingModeEnum | null | undefined,
): DefaultProductFiltersMapType => {
    const defaultProductFiltersMap = getEmptyDefaultProductFiltersMap();

    for (const flagOption of productFilterOptions?.flags || []) {
        if (flagOption.isSelected) {
            defaultProductFiltersMap.flags.add(flagOption.flag.uuid);
        }
    }

    if (defaultOrderingMode) {
        defaultProductFiltersMap.sort = defaultOrderingMode;
    }

    for (const filterOptionParameter of productFilterOptions?.parameters || []) {
        if (!('values' in filterOptionParameter)) {
            continue;
        }

        for (const filterOptionParameterValue of filterOptionParameter.values) {
            if (filterOptionParameterValue.isSelected) {
                const mapValue = defaultProductFiltersMap.parameters.get(filterOptionParameter.uuid) || new Set();
                mapValue.add(filterOptionParameterValue.uuid);
                defaultProductFiltersMap.parameters.set(filterOptionParameter.uuid, mapValue);
            }
        }
    }

    return defaultProductFiltersMap;
};

export const getHasDefaultFilters = (defaultProductFiltersMap: DefaultProductFiltersMapType) =>
    defaultProductFiltersMap.flags.size > 0 || defaultProductFiltersMap.parameters.size > 0;

export const getChangedDefaultFiltersAfterFlagChange = (
    defaultProductFiltersMap: DefaultProductFiltersMapType,
    filter: FilterOptionsUrlQueryType | null,
    changedFlagUuid: string,
): FilterOptionsUrlQueryType => {
    if (!defaultProductFiltersMap.flags.delete(changedFlagUuid)) {
        defaultProductFiltersMap.flags.add(changedFlagUuid);
    }

    return getChangedDefaultFilters(defaultProductFiltersMap, filter);
};

export const getChangedDefaultFiltersAfterBrandChange = (
    defaultProductFiltersMap: DefaultProductFiltersMapType,
    filter: FilterOptionsUrlQueryType | null,
    changedBrandUuid: string,
): FilterOptionsUrlQueryType => {
    if (!defaultProductFiltersMap.brands.delete(changedBrandUuid)) {
        defaultProductFiltersMap.brands.add(changedBrandUuid);
    }

    return getChangedDefaultFilters(defaultProductFiltersMap, filter);
};

export const getChangedDefaultFiltersAfterParameterChange = (
    defaultProductFiltersMap: DefaultProductFiltersMapType,
    filter: FilterOptionsUrlQueryType | null,
    changedParameterUuid: string,
    changedParameterValueUuid: string,
): FilterOptionsUrlQueryType => {
    const matchingDefaultParameter = defaultProductFiltersMap.parameters.get(changedParameterUuid);
    if (matchingDefaultParameter) {
        if (!matchingDefaultParameter.delete(changedParameterValueUuid)) {
            matchingDefaultParameter.add(changedParameterValueUuid);
        }
    } else {
        defaultProductFiltersMap.parameters.set(changedParameterUuid, new Set([changedParameterValueUuid]));
    }

    return getChangedDefaultFilters(defaultProductFiltersMap, filter);
};

export const getChangedDefaultFiltersAfterSliderParameterChange = (
    defaultProductFiltersMap: DefaultProductFiltersMapType,
    filter: FilterOptionsUrlQueryType | null,
    changedaParameterUuid: string,
    minimalValue: number | undefined,
    maximalValue: number | undefined,
): FilterOptionsUrlQueryType => {
    const selectedParameters: FilterOptionsParameterUrlQueryType[] = Array.from(defaultProductFiltersMap.parameters)
        .map(([defaultParameterUuid, defaultParameterValues]) => ({
            parameter: defaultParameterUuid,
            values: Array.from(defaultParameterValues),
        }))
        .filter(({ values }) => values.length !== 0);

    selectedParameters.push({
        parameter: changedaParameterUuid,
        minimalValue,
        maximalValue,
    });

    return {
        ...filter,
        flags: Array.from(defaultProductFiltersMap.flags),
        parameters: selectedParameters,
    };
};

export const getChangedDefaultFiltersAfterPriceChange = (
    defaultProductFiltersMap: DefaultProductFiltersMapType,
    filter: FilterOptionsUrlQueryType | null,
    newMinPrice: number | undefined,
    newMaxPrice: number | undefined,
): FilterOptionsUrlQueryType => {
    return getChangedDefaultFilters(defaultProductFiltersMap, {
        ...filter,
        minimalPrice: newMinPrice,
        maximalPrice: newMaxPrice,
    });
};

export const getChangedDefaultFiltersAfterMinimumPriceChange = (
    defaultProductFiltersMap: DefaultProductFiltersMapType,
    filter: FilterOptionsUrlQueryType | null,
    newMinPrice: number | undefined,
): FilterOptionsUrlQueryType => {
    return getChangedDefaultFilters(defaultProductFiltersMap, { ...filter, minimalPrice: newMinPrice });
};

export const getChangedDefaultFiltersAfterMaximumPriceChange = (
    defaultProductFiltersMap: DefaultProductFiltersMapType,
    filter: FilterOptionsUrlQueryType | null,
    newMaxPrice: number | undefined,
): FilterOptionsUrlQueryType => {
    return getChangedDefaultFilters(defaultProductFiltersMap, { ...filter, maximalPrice: newMaxPrice });
};

export const getChangedDefaultFiltersAfterAvailabilityChange = (
    defaultProductFiltersMap: DefaultProductFiltersMapType,
    filter: FilterOptionsUrlQueryType | null,
    isOnlyInStock: boolean,
): FilterOptionsUrlQueryType => {
    return getChangedDefaultFilters(defaultProductFiltersMap, { ...filter, onlyInStock: isOnlyInStock });
};

export const getChangedDefaultFilters = (
    defaultFilter: DefaultProductFiltersMapType,
    updatedFilter: FilterOptionsUrlQueryType | null,
): FilterOptionsUrlQueryType => ({
    onlyInStock: updatedFilter?.onlyInStock,
    minimalPrice: updatedFilter?.minimalPrice,
    maximalPrice: updatedFilter?.maximalPrice,
    brands: mergeNullableArrays(Array.from(defaultFilter.brands), updatedFilter?.brands),
    flags: mergeNullableArrays(Array.from(defaultFilter.flags), updatedFilter?.flags),
    parameters: Array.from(defaultFilter.parameters)
        .map(([defaultParameterUuid, defaultParameterValues]) => ({
            parameter: defaultParameterUuid,
            values: Array.from(defaultParameterValues),
        }))
        .filter(({ values }) => values.length !== 0),
});

export const getFilterWithoutSeoSensitiveFilters = (
    currentFilter: FilterOptionsUrlQueryType | undefined | null,
    currentSort: ProductOrderingModeEnum | null,
) => {
    const filteredSort = SEO_SENSITIVE_FILTERS.SORT || !currentSort ? undefined : currentSort;
    if (!currentFilter) {
        return { filteredFilter: undefined, filteredSort };
    }

    const filteredFilter: Partial<FilterOptionsUrlQueryType> = { ...currentFilter };
    if (SEO_SENSITIVE_FILTERS.AVAILABILITY) {
        delete filteredFilter.onlyInStock;
    }
    if (SEO_SENSITIVE_FILTERS.BRANDS) {
        delete filteredFilter.brands;
    }
    if (SEO_SENSITIVE_FILTERS.FLAGS) {
        delete filteredFilter.flags;
    }
    if (SEO_SENSITIVE_FILTERS.PARAMETERS.CHECKBOX) {
        filteredFilter.parameters = filteredFilter.parameters?.filter(
            (parameter) => typeof parameter.minimalValue === 'number' && typeof parameter.maximalValue === 'number',
        );
    }
    if (SEO_SENSITIVE_FILTERS.PARAMETERS.SLIDER) {
        filteredFilter.parameters = filteredFilter.parameters?.filter((parameter) => !!parameter.values?.length);
    }
    if (!filteredFilter.parameters?.length) {
        delete filteredFilter.parameters;
    }
    if (SEO_SENSITIVE_FILTERS.PRICE) {
        delete filteredFilter.minimalPrice;
    }
    if (SEO_SENSITIVE_FILTERS.PRICE) {
        delete filteredFilter.maximalPrice;
    }

    return { filteredFilter, filteredSort };
};

export const useRedirectFromSeoCategory = () => {
    const setWasRedirectedFromSeoCategory = useSessionStore((s) => s.setWasRedirectedFromSeoCategory);

    const redirectFromSeoCategory = (pushQueryFilter: () => void) => {
        setWasRedirectedFromSeoCategory(true);
        pushQueryFilter();
    };

    return redirectFromSeoCategory;
};
