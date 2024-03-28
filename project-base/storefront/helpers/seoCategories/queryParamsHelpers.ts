import { mergeNullableArrays } from 'helpers/arrays/mergeNullableArrays';
import { DefaultProductFiltersMapType } from 'store/slices/createSeoCategorySlice';
import { useSessionStore } from 'store/useSessionStore';
import { FilterOptionsParameterUrlQueryType, FilterOptionsUrlQueryType } from 'types/productFilter';

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

export const useRedirectFromSeoCategory = () => {
    const setWasRedirectedFromSeoCategory = useSessionStore((s) => s.setWasRedirectedFromSeoCategory);

    const redirectFromSeoCategory = (pushQueryFilter: () => void) => {
        setWasRedirectedFromSeoCategory(true);
        pushQueryFilter();
    };

    return redirectFromSeoCategory;
};
