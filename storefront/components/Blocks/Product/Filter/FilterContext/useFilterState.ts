import { FilterContext } from './context';
import { FilterActions, FilterState } from './types';
import { getActualUrlQueryWithoutDefaultPriceFilter } from 'helpers/filterOptions/getActualUrlQueryWithoutDefaultPriceFilter';
import { getIsProductFilterEmpty } from 'helpers/filterOptions/getIsProductFilterEmpty';
import { getIsProductFilterSameAsDefault } from 'helpers/filterOptions/getIsProductFilterSameAsDefault';
import { Dispatch, useContext, useMemo } from 'react';
import {
    FilterFormBrandType,
    FilterFormFlagType,
    FilterFormParameterType,
    FilterOptionsType,
} from 'types/productFilter';

export const useFilterState = (): [FilterState, Dispatch<FilterActions>] => useContext(FilterContext)!;

export const useCheckedBrands = (): FilterFormBrandType[] => {
    const [state] = useFilterState();
    return useMemo(() => state.selected.brands.filter((i) => i.checked), [state.selected.brands]);
};

export const useCheckedFlags = (): FilterFormFlagType[] => {
    const [state] = useFilterState();
    return useMemo(() => state.selected.flags.filter((i) => i.checked), [state.selected.flags]);
};

export const useCheckedParameters = (): FilterFormParameterType[] => {
    const [state] = useFilterState();
    return useMemo(
        () =>
            state.selected.parameters.filter((current) => {
                const filteredValues = current.values.filter((v) => v.checked);

                if (filteredValues.length > 0) {
                    return { ...current, values: filteredValues };
                } else if ('minimalValue' in current && 'maximalValue' in current) {
                    const parameter = state.options.parameters?.find((p) => p.uuid === current.parameterUuid);
                    if (
                        (current.minimalValue !== null &&
                            parameter &&
                            'minimalValue' in parameter &&
                            parameter.minimalValue !== current.minimalValue) ||
                        (current.maximalValue !== null &&
                            parameter &&
                            'maximalValue' in parameter &&
                            parameter.maximalValue !== current.maximalValue)
                    ) {
                        return { ...current, values: [] };
                    }
                }
                return false;
            }),
        [state.options.parameters, state.selected.parameters],
    );
};

export const useIsProductFilterEmpty = (): boolean => {
    const checkedBrands = useCheckedBrands();
    const checkedFlags = useCheckedFlags();
    const checkedParameters = useCheckedParameters();
    const [state] = useFilterState();
    const minimalPrice = useMemo(() => state.selected.minimalPrice, [state.selected.minimalPrice]);
    const maximalPrice = useMemo(() => state.selected.maximalPrice, [state.selected.maximalPrice]);
    const onlyInStock = useMemo(() => state.selected.onlyInStock, [state.selected.onlyInStock]);
    const options = useMemo(() => state.options, [state.options]);

    return getIsProductFilterEmpty(
        checkedBrands,
        checkedFlags,
        minimalPrice,
        maximalPrice,
        onlyInStock,
        checkedParameters,
        options,
    );
};

export const useIsProductFilterSameAsDefault = (): boolean => {
    const checkedBrands = useCheckedBrands();
    const checkedFlags = useCheckedFlags();
    const checkedParameters = useCheckedParameters();
    const [state] = useFilterState();
    const minimalPrice = useMemo(() => state.selected.minimalPrice, [state.selected.minimalPrice]);
    const maximalPrice = useMemo(() => state.selected.maximalPrice, [state.selected.maximalPrice]);
    const onlyInStock = useMemo(() => state.selected.onlyInStock, [state.selected.onlyInStock]);
    const options = useMemo(() => state.options, [state.options]);

    return getIsProductFilterSameAsDefault(
        checkedBrands,
        checkedFlags,
        minimalPrice,
        maximalPrice,
        onlyInStock,
        checkedParameters,
        options,
    );
};

export const useActualUrlQueryWithoutDefaultPriceFilter = (): string | undefined => {
    const checkedBrands = useCheckedBrands();
    const checkedFlags = useCheckedFlags();
    const checkedParameters = useCheckedParameters();
    const [state] = useFilterState();
    const minimalPrice = useMemo(() => state.selected.minimalPrice, [state.selected.minimalPrice]);
    const maximalPrice = useMemo(() => state.selected.maximalPrice, [state.selected.maximalPrice]);
    const onlyInStock = useMemo(() => state.selected.onlyInStock, [state.selected.onlyInStock]);
    const options = useMemo(() => state.options, [state.options]);

    return getActualUrlQueryWithoutDefaultPriceFilter(
        checkedBrands,
        checkedFlags,
        minimalPrice,
        maximalPrice,
        onlyInStock,
        checkedParameters,
        options,
    );
};

export const useProductFilterOptions = (): FilterOptionsType | null => {
    const state = useFilterState();

    return useMemo(() => {
        if (typeof state !== 'undefined' && typeof state[0] !== 'undefined') {
            return state[0].options;
        }
        return null;
    }, [state]);
};
