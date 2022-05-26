import { ProductFilterOptionsFragmentApi } from 'graphql/generated';
import { FilterOptionsParameterTypeEnum, FilterOptionsType } from 'types/productFilter';

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
        parameters: productFilterOptionsApiData.parameters?.map((item) => ({
            ...item,
            type:
                item.__typename === FilterOptionsParameterTypeEnum.ColorPicker
                    ? FilterOptionsParameterTypeEnum.ColorPicker
                    : FilterOptionsParameterTypeEnum.Checkbox,
            values: item.values.map((value) => ({
                ...value,
                rgbHex: value.rgbHex !== null ? value.rgbHex : undefined,
            })),
        })),
        currencyCode,
    };
};
