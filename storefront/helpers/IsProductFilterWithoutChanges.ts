import { FilterOptionsStateType, FilterOptionsType } from 'types/productFilter';

export const isProductFilterWithoutChanges = (
    parametersFilterState: FilterOptionsStateType,
    productFilterOptions: FilterOptionsType,
): boolean => {
    return (
        parametersFilterState.brands.length === 0 &&
        parametersFilterState.flags.filter(
            (flag) => !productFilterOptions.flags.find((productFlag) => productFlag.flag.uuid === flag)?.isSelected,
        ).length === 0 &&
        parametersFilterState.parameters.filter(
            (parameter) =>
                parameter.values.length > 0 || parameter.minimalValue !== null || parameter.maximalValue !== null,
        ).length === 0 &&
        parametersFilterState.onlyInStock === false &&
        (parametersFilterState.minimalPrice === productFilterOptions.minimalPrice ||
            parametersFilterState.minimalPrice === null) &&
        (parametersFilterState.maximalPrice === productFilterOptions.maximalPrice ||
            parametersFilterState.maximalPrice === null)
    );
};
