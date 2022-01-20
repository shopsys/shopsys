import { FilterOptionsParameterTypeEnum, FilterOptionsType } from 'types/productFilter';
import { ProductFilterOptionsFragmentApi } from 'graphql/generated';

export const mapProductFilterOptions = (
    productFilterOptionsApiData: ProductFilterOptionsFragmentApi,
    currencyCode: string,
): FilterOptionsType | null => {
    return {
        ...productFilterOptionsApiData,
        minimalPrice: Math.round((parseFloat(productFilterOptionsApiData.minimalPrice) + Number.EPSILON) * 100) / 100,
        maximalPrice: Math.round((parseFloat(productFilterOptionsApiData.maximalPrice) + Number.EPSILON) * 100) / 100,
        brands:
            productFilterOptionsApiData.brands !== null && productFilterOptionsApiData.brands !== undefined
                ? productFilterOptionsApiData.brands
                : [],
        flags: productFilterOptionsApiData.flags !== null ? productFilterOptionsApiData.flags : [],
        parameters: productFilterOptionsApiData.parameters?.map((item) => ({
            ...item,
            type:
                item.type === FilterOptionsParameterTypeEnum.ColorPicker
                    ? FilterOptionsParameterTypeEnum.ColorPicker
                    : FilterOptionsParameterTypeEnum.Checkbox,
            values: item.values.map((value) => ({
                ...value,
                rgbHex: value.rgbHex !== undefined && value.rgbHex !== null ? value.rgbHex : undefined,
            })),
        })),
        currencyCode,
    };
};
