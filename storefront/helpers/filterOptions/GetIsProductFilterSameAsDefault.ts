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
    const checkedFlagsSet = checkedFlags.map((checkedFlag) => checkedFlag.uuid);
    const defaultCheckedFlagsSet = productFilterOptions.flags.reduce((array: string[], flag) => {
        if (flag.isSelected) {
            array.push(flag.flag.uuid);
        }

        return array;
    }, []);

    return (
        checkedFlagsSet.every((flag) => defaultCheckedFlagsSet.includes(flag)) &&
        checkedFlags.length === defaultCheckedFlagsSet.length
    );
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
            const matchingDefaultParameterValue = defaultParameter.values.find(
                (defaultValue) => defaultValue.uuid === value.uuid,
            );

            if (matchingDefaultParameterValue === undefined) {
                return false;
            }

            if (value.checked !== matchingDefaultParameterValue.isSelected) {
                return false;
            }
        }
    }

    return true;
};
