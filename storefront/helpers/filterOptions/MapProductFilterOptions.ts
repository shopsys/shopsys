import { FilterOptionsParameterTypeEnum, FilterOptionsType } from 'types/productFilter';
import { ProductFilterOptionsFragmentApi } from 'graphql/generated';

export const mapProductFilterOptions = (
    productFilterOptionsApiData: ProductFilterOptionsFragmentApi,
    currencyCode: string,
): FilterOptionsType | null => {
    return {
        ...productFilterOptionsApiData,
        minimalPrice: parseFloat(productFilterOptionsApiData.minimalPrice),
        maximalPrice: parseFloat(productFilterOptionsApiData.maximalPrice),
        brands:
            productFilterOptionsApiData.brands !== null && productFilterOptionsApiData.brands !== undefined
                ? productFilterOptionsApiData.brands
                : [],
        flags:
            productFilterOptionsApiData.flags !== null && productFilterOptionsApiData.flags !== undefined
                ? productFilterOptionsApiData.flags
                : [],
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
