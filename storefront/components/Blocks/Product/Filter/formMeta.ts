import { ProductFilterApi } from 'graphql/generated';
import {
    FilterFormBrandType,
    FilterFormFlagType,
    FilterFormParameterType,
    FilterFormParameterValuesType,
    FilterFormType,
    FilterOptionsType,
    ParametersType,
} from 'types/productFilter';

const getDefaultBrandValues = (
    queryFromUrl: ProductFilterApi | null,
    productFilterOptions: FilterOptionsType,
    originalSlug: string | null,
): FilterFormBrandType[] => {
    return productFilterOptions.brands.map((value) => ({
        ...value.brand,
        checked: originalSlug === null ? !!queryFromUrl?.brands?.includes(value.brand.uuid) : false,
    }));
};

const getDefaultFlagValues = (
    queryFromUrl: ProductFilterApi | null,
    productFilterOptions: FilterOptionsType,
    originalSlug: string | null,
): FilterFormFlagType[] => {
    return productFilterOptions.flags.map((value) => ({
        ...value.flag,
        checked: originalSlug === null ? !!queryFromUrl?.flags?.includes(value.flag.uuid) : value.isSelected,
    }));
};

const getDefaultParameterValues = (
    queryFromUrl: ProductFilterApi | null,
    productFilterOptions: FilterOptionsType,
    originalSlug: string | null,
): FilterFormParameterType[] => {
    if (productFilterOptions.parameters === undefined) {
        return [];
    }

    const getValues = (parameter: ParametersType): FilterFormParameterValuesType[] => {
        if (!('values' in parameter)) {
            return [];
        }

        return parameter.values.map((value) => {
            return {
                id: value.uuid,
                uuid: value.uuid,
                text: value.text,
                checked:
                    originalSlug === null
                        ? !!queryFromUrl?.parameters
                              ?.find((parameterFromUrl) => parameterFromUrl.parameter === parameter.uuid)
                              ?.values?.includes(value.uuid)
                        : value.isSelected,
                rgbHex: 'rgbHex' in value ? value.rgbHex : null,
            };
        });
    };

    return productFilterOptions.parameters.map((parameter) => {
        return {
            parameterName: parameter.name,
            parameterUuid: parameter.uuid,
            values: getValues(parameter),
            isCollapsed: parameter.isCollapsed,
            minimalValue: 'minimalValue' in parameter ? parameter.minimalValue : null,
            maximalValue: 'maximalValue' in parameter ? parameter.maximalValue : null,
            selectedValue: 'selectedValue' in parameter ? parameter.selectedValue : null,
            unit: 'unit' in parameter ? parameter.unit : null,
        };
    });
};

const getDefaultStockAvailability = (queryFromUrl: ProductFilterApi | null, originalSlug: string | null): boolean =>
    queryFromUrl?.onlyInStock !== undefined && queryFromUrl.onlyInStock !== null && originalSlug === null
        ? queryFromUrl.onlyInStock
        : false;

const getDefaultMinimalPrice = (
    queryFromUrl: ProductFilterApi | null,
    productFilterOptions: FilterOptionsType,
    originalSlug: string | null,
): number =>
    queryFromUrl?.minimalPrice !== undefined && queryFromUrl.minimalPrice !== null && originalSlug === null
        ? Number.parseFloat(queryFromUrl.minimalPrice)
        : productFilterOptions.minimalPrice;

const getDefaultMaximalPrice = (
    queryFromUrl: ProductFilterApi | null,
    productFilterOptions: FilterOptionsType,
    originalSlug: string | null,
): number =>
    queryFromUrl?.maximalPrice !== undefined && queryFromUrl.maximalPrice !== null && originalSlug === null
        ? Number.parseFloat(queryFromUrl.maximalPrice)
        : productFilterOptions.maximalPrice;

export const getDefaultFormValues = (
    queryFromUrl: ProductFilterApi | null,
    productFilterOptions: FilterOptionsType,
    originalSlug: string | null,
): FilterFormType => {
    return {
        brands: getDefaultBrandValues(queryFromUrl, productFilterOptions, originalSlug),
        flags: getDefaultFlagValues(queryFromUrl, productFilterOptions, originalSlug),
        parameters: getDefaultParameterValues(queryFromUrl, productFilterOptions, originalSlug),
        onlyInStock: getDefaultStockAvailability(queryFromUrl, originalSlug),
        minimalPrice: getDefaultMinimalPrice(queryFromUrl, productFilterOptions, originalSlug),
        maximalPrice: getDefaultMaximalPrice(queryFromUrl, productFilterOptions, originalSlug),
    };
};
