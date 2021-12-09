import { FilterOptionsStateType, FilterOptionsType } from 'components/Blocks/Product/Filter/types';

export const isProductFilterWithoutChanges = (
    parametersFilterState: FilterOptionsStateType,
    productFilterOptions: FilterOptionsType,
): boolean => {
    return (
        parametersFilterState.brands.length === 0 &&
        parametersFilterState.flags.length === 0 &&
        parametersFilterState.parameters.length === 0 &&
        parametersFilterState.onlyInStock === false &&
        (parametersFilterState.minimalPrice === productFilterOptions.minimalPrice ||
            parametersFilterState.minimalPrice === null) &&
        (parametersFilterState.maximalPrice === productFilterOptions.maximalPrice ||
            parametersFilterState.maximalPrice === null)
    );
};
