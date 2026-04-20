import { DefaultProductFiltersMapType } from 'store/slices/createSeoCategorySlice';
import { useSessionStore } from 'store/useSessionStore';
import { FilterOptionsParameterUrlQueryType, FilterOptionsUrlQueryType } from 'types/productFilter';

const isSliderParameter = (parameter: FilterOptionsParameterUrlQueryType) =>
    typeof parameter.minimalValue === 'number' || typeof parameter.maximalValue === 'number';

const mergeUniqueArrays = <T>(...values: Array<T[] | undefined | null>): T[] =>
    Array.from(new Set(values.flatMap((value) => value ?? [])));

const toggleSetValue = (set: Set<string>, changedUuid: string): void => {
    if (!set.delete(changedUuid)) {
        set.add(changedUuid);
    }
};

const buildMergedParameters = (
    defaultParameters: DefaultProductFiltersMapType['parameters'],
    updatedParameters: FilterOptionsParameterUrlQueryType[] | undefined,
): FilterOptionsParameterUrlQueryType[] => {
    const checkboxParametersMap = new Map(
        Array.from(defaultParameters).map(([parameterUuid, parameterValues]) => [
            parameterUuid,
            new Set(parameterValues),
        ]),
    );
    const sliderParametersMap = new Map<string, FilterOptionsParameterUrlQueryType>();

    for (const parameter of updatedParameters ?? []) {
        if (isSliderParameter(parameter)) {
            sliderParametersMap.set(parameter.parameter, {
                parameter: parameter.parameter,
                minimalValue: parameter.minimalValue,
                maximalValue: parameter.maximalValue,
            });

            continue;
        }

        const values = checkboxParametersMap.get(parameter.parameter) ?? new Set<string>();

        for (const value of parameter.values ?? []) {
            values.add(value);
        }

        checkboxParametersMap.set(parameter.parameter, values);
    }

    const checkboxParameters = Array.from(checkboxParametersMap)
        .map(([parameterUuid, parameterValues]) => ({
            parameter: parameterUuid,
            values: Array.from(parameterValues),
        }))
        .filter(({ values }) => values.length > 0);

    return [...checkboxParameters, ...Array.from(sliderParametersMap.values())];
};

const getChangedDefaultFiltersWithOverride = (
    defaultProductFiltersMap: DefaultProductFiltersMapType,
    filter: FilterOptionsUrlQueryType | null,
    override: Partial<FilterOptionsUrlQueryType>,
): FilterOptionsUrlQueryType => getChangedDefaultFilters(defaultProductFiltersMap, { ...filter, ...override });

export const getChangedDefaultFiltersAfterFlagChange = (
    defaultProductFiltersMap: DefaultProductFiltersMapType,
    filter: FilterOptionsUrlQueryType | null,
    changedFlagUuid: string,
): FilterOptionsUrlQueryType => {
    toggleSetValue(defaultProductFiltersMap.flags, changedFlagUuid);

    return getChangedDefaultFiltersWithOverride(defaultProductFiltersMap, filter, { flags: undefined });
};

export const getChangedDefaultFiltersAfterBrandChange = (
    defaultProductFiltersMap: DefaultProductFiltersMapType,
    filter: FilterOptionsUrlQueryType | null,
    changedBrandUuid: string,
): FilterOptionsUrlQueryType => {
    toggleSetValue(defaultProductFiltersMap.brands, changedBrandUuid);

    return getChangedDefaultFiltersWithOverride(defaultProductFiltersMap, filter, { brands: undefined });
};

export const getChangedDefaultFiltersAfterParameterChange = (
    defaultProductFiltersMap: DefaultProductFiltersMapType,
    filter: FilterOptionsUrlQueryType | null,
    changedParameterUuid: string,
    changedParameterValueUuid: string,
): FilterOptionsUrlQueryType => {
    const selectedParameterValues = defaultProductFiltersMap.parameters.get(changedParameterUuid) ?? new Set<string>();
    toggleSetValue(selectedParameterValues, changedParameterValueUuid);
    defaultProductFiltersMap.parameters.set(changedParameterUuid, selectedParameterValues);

    return getChangedDefaultFiltersWithOverride(defaultProductFiltersMap, filter, {
        parameters: filter?.parameters?.filter(isSliderParameter),
    });
};

export const getChangedDefaultFiltersAfterSliderParameterChange = (
    defaultProductFiltersMap: DefaultProductFiltersMapType,
    filter: FilterOptionsUrlQueryType | null,
    changedParameterUuid: string,
    minimalValue: number | undefined,
    maximalValue: number | undefined,
): FilterOptionsUrlQueryType => {
    const updatedSliderParameters = (filter?.parameters ?? []).filter(
        (parameter) => isSliderParameter(parameter) && parameter.parameter !== changedParameterUuid,
    );

    if (typeof minimalValue === 'number' || typeof maximalValue === 'number') {
        updatedSliderParameters.push({
            parameter: changedParameterUuid,
            minimalValue,
            maximalValue,
        });
    }

    return getChangedDefaultFiltersWithOverride(defaultProductFiltersMap, filter, {
        parameters: updatedSliderParameters,
    });
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
    brands: mergeUniqueArrays(Array.from(defaultFilter.brands), updatedFilter?.brands),
    flags: mergeUniqueArrays(Array.from(defaultFilter.flags), updatedFilter?.flags),
    parameters: buildMergedParameters(defaultFilter.parameters, updatedFilter?.parameters),
});

export const useRedirectFromSeoCategory = () => {
    const setWasRedirectedFromSeoCategory = useSessionStore((s) => s.setWasRedirectedFromSeoCategory);

    const redirectFromSeoCategory = (pushQueryFilter: () => void) => {
        setWasRedirectedFromSeoCategory(true);
        pushQueryFilter();
    };

    return redirectFromSeoCategory;
};
