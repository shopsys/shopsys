import {
    FilterFormBrandType,
    FilterFormFlagType,
    FilterFormParameterType,
    FilterOptionsType,
} from 'types/productFilter';

export const getIsProductFilterSameAsDefault = (
    checkedBrands: FilterFormBrandType[],
    checkedFlags: FilterFormFlagType[],
    currentMinimalPrice: number | null,
    currentMaximalPrice: number | null,
    onlyInStock: boolean,
    checkedParameters: FilterFormParameterType[],
    productFilterOptions: FilterOptionsType,
): boolean =>
    areBrandFiltersWithoutChanges(checkedBrands) &&
    areFlagFiltersWithoutChanges(checkedFlags, productFilterOptions) &&
    isMinimalPriceFilterWithoutChanges(currentMinimalPrice, productFilterOptions) &&
    isMaximalPriceFilterWithoutChanges(currentMaximalPrice, productFilterOptions) &&
    isStockAvailabilityFilterWithoutChanges(onlyInStock) &&
    areParameterFiltersWithoutChanges(checkedParameters, productFilterOptions);

export const areBrandFiltersWithoutChanges = (checkedBrands: FilterFormBrandType[]): boolean => {
    return checkedBrands.length === 0;
};

export const areFlagFiltersWithoutChanges = (
    checkedFlags: FilterFormFlagType[],
    productFilterOptions: FilterOptionsType,
): boolean => {
    const checkedFlagsSet = new Set(checkedFlags.map((checkedFlag) => checkedFlag.uuid));
    productFilterOptions.flags.forEach((defaultFlag) => {
        if (defaultFlag.isSelected) {
            checkedFlagsSet.delete(defaultFlag.flag.uuid);
        }
    });

    return checkedFlagsSet.size === 0;
};

export const isMinimalPriceFilterWithoutChanges = (
    currentMinimalPrice: number | null,
    productFilterOptions: FilterOptionsType,
): boolean => {
    return currentMinimalPrice === productFilterOptions.minimalPrice;
};

export const isMaximalPriceFilterWithoutChanges = (
    currentMaximalPrice: number | null,
    productFilterOptions: FilterOptionsType,
): boolean => {
    return currentMaximalPrice === productFilterOptions.maximalPrice;
};

export const isStockAvailabilityFilterWithoutChanges = (onlyInStock: boolean): boolean => {
    return onlyInStock === false;
};

export const areParameterFiltersWithoutChanges = (
    checkedParameters: FilterFormParameterType[],
    productFilterOptions: FilterOptionsType,
): boolean => {
    const defaultCheckedParameters =
        productFilterOptions.parameters?.filter((parameter) =>
            'values' in parameter ? parameter.values.some((value) => value.isSelected) : false,
        ) ?? [];

    if (defaultCheckedParameters.length !== checkedParameters.length) {
        return false;
    }

    for (const checkedParameter of checkedParameters) {
        const defaultParameter = defaultCheckedParameters.find(
            (parameter) => parameter.uuid === checkedParameter.parameterUuid,
        );

        if (defaultParameter === undefined) {
            return false;
        }

        if (!('values' in defaultParameter)) {
            if (
                defaultParameter.minimalValue !== checkedParameter.minimalValue ||
                defaultParameter.maximalValue !== checkedParameter.maximalValue
            ) {
                return false;
            }

            continue;
        }

        for (const value of checkedParameter.values) {
            if (!defaultParameter.values.some((defaultValue) => defaultValue.uuid === value.uuid)) {
                return false;
            }
        }
    }

    return true;
};
