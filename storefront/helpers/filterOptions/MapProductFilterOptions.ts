import { ProductFilterOptionsFragmentApi } from 'graphql/generated';
import { FilterOptionsType } from 'types/productFilter';

export const mapProductFilterOptions = (
    productFilterOptionsApiData: ProductFilterOptionsFragmentApi,
    currencyCode: string,
): FilterOptionsType | null => {
    return {
        ...productFilterOptionsApiData,
        minimalPrice: Math.round((parseFloat(productFilterOptionsApiData.minimalPrice) + Number.EPSILON) * 100) / 100,
        maximalPrice: Math.round((parseFloat(productFilterOptionsApiData.maximalPrice) + Number.EPSILON) * 100) / 100,
        brands: productFilterOptionsApiData.brands !== null ? productFilterOptionsApiData.brands : [],
        flags: productFilterOptionsApiData.flags !== null ? productFilterOptionsApiData.flags : [],
        parameters: productFilterOptionsApiData.parameters ?? undefined,
        currencyCode,
    };
};
