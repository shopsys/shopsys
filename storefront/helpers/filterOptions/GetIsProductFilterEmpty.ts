import {
    FilterFormBrandType,
    FilterFormFlagType,
    FilterFormParameterType,
    FilterOptionsType,
} from 'types/productFilter';

export const getIsProductFilterEmpty = (
    checkedBrands: FilterFormBrandType[],
    checkedFlags: FilterFormFlagType[],
    currentMinimalPrice: number | null,
    currentMaximalPrice: number | null,
    onlyInStock: boolean,
    checkedParameters: FilterFormParameterType[],
    productFilterOptions: FilterOptionsType,
): boolean => {
    return (
        checkedBrands.length === 0 &&
        checkedFlags.length === 0 &&
        checkedParameters.length === 0 &&
        onlyInStock === false &&
        currentMinimalPrice === productFilterOptions.minimalPrice &&
        currentMaximalPrice === productFilterOptions.maximalPrice
    );
};
