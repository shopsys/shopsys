import { ProductFilterOptionsFragmentApi } from 'graphql/generated';
import { mapPriceForCalculations, roundPrice } from 'helpers/mappers/price';
import { FilterFormBrandType, FilterFormFlagType, FilterFormParameterType } from 'types/productFilter';

export const getIsProductFilterEmpty = (
    checkedBrands: FilterFormBrandType[],
    checkedFlags: FilterFormFlagType[],
    currentMinimalPrice: number | null,
    currentMaximalPrice: number | null,
    onlyInStock: boolean,
    checkedParameters: FilterFormParameterType[],
    productFilterOptions: ProductFilterOptionsFragmentApi,
): boolean => {
    return (
        checkedBrands.length === 0 &&
        checkedFlags.length === 0 &&
        checkedParameters.length === 0 &&
        onlyInStock === false &&
        currentMinimalPrice === roundPrice(mapPriceForCalculations(productFilterOptions.minimalPrice)) &&
        currentMaximalPrice === roundPrice(mapPriceForCalculations(productFilterOptions.maximalPrice))
    );
};
