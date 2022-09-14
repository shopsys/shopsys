import {
    isMaximalPriceFilterWithoutChanges,
    isMinimalPriceFilterWithoutChanges,
} from './getIsProductFilterSameAsDefault';
import {
    FilterFormBrandType,
    FilterFormFlagType,
    FilterFormParameterType,
    FilterOptionsType,
    FilterOptionsUrlQueryType,
} from 'types/productFilter';

export const getActualUrlQueryWithoutDefaultPriceFilter = (
    checkedBrands: FilterFormBrandType[],
    checkedFlags: FilterFormFlagType[],
    currentMinimalPrice: number | null,
    currentMaximalPrice: number | null,
    onlyInStock: boolean,
    checkedParameters: FilterFormParameterType[],
    productFilterOptions: FilterOptionsType,
): string => {
    const filterQueryObject: FilterOptionsUrlQueryType = {
        brands: checkedBrands.map((brand) => brand.uuid),
        flags: checkedFlags.map((flag) => flag.uuid),
        onlyInStock,
        parameters: getActualCheckedParametersForUrlQuery(checkedParameters),
        minimalPrice:
            isMinimalPriceFilterWithoutChanges(currentMinimalPrice, productFilterOptions) ||
            currentMinimalPrice === null
                ? undefined
                : currentMinimalPrice,
        maximalPrice:
            isMaximalPriceFilterWithoutChanges(currentMaximalPrice, productFilterOptions) ||
            currentMaximalPrice === null
                ? undefined
                : currentMaximalPrice,
    };

    return JSON.stringify(filterQueryObject);
};

const getActualCheckedParametersForUrlQuery = (checkedParameters: FilterFormParameterType[]) => {
    const parameters = [];

    for (const parameter of checkedParameters) {
        const checkedValues = [];

        for (const value of parameter.values) {
            if (value.checked) {
                checkedValues.push(value.uuid);
            }
        }

        if (checkedValues.length === 0 && parameter.minimalValue === null && parameter.maximalValue === null) {
            continue;
        }

        parameters.push({
            parameter: parameter.parameterUuid,
            values: checkedValues,
            minimalValue: parameter.minimalValue ?? null,
            maximalValue: parameter.maximalValue ?? null,
        });
    }

    return parameters;
};
