import { getActualUrlQueryWithoutDefaultPriceFilter } from './getActualUrlQueryWithoutDefaultPriceFilter';
import { ProductFilterOptionsFragmentApi } from 'graphql/generated';
import { FilterFormFlagType, FilterFormParameterType } from 'types/productFilter';

export const getFilterUrlQueryForSortingInSeoCategory = (
    productFilterOptions: ProductFilterOptionsFragmentApi,
): string | null => {
    const checkedFlags: FilterFormFlagType[] =
        productFilterOptions.flags?.reduce((array: FilterFormFlagType[], flag) => {
            if (flag.isSelected) {
                array.push({ name: flag.flag.name, uuid: flag.flag.uuid, checked: true });
            }
            return array;
        }, []) ?? [];

    const checkedParameters: FilterFormParameterType[] =
        productFilterOptions.parameters?.reduce((array: FilterFormParameterType[], parameter) => {
            const isSomeParameterValueSelected =
                'values' in parameter && parameter.values.some((value) => value.isSelected);
            const hasParameterSelectedValue = 'selectedValue' in parameter && parameter.selectedValue !== null;

            if (isSomeParameterValueSelected || hasParameterSelectedValue) {
                const unmappedParameterValues = 'values' in parameter ? parameter.values : [];
                const mappedParameterValues = [];
                for (const unmappedParameterValue of unmappedParameterValues) {
                    if (unmappedParameterValue.isSelected) {
                        mappedParameterValues.push({
                            checked: unmappedParameterValue.isSelected,
                            uuid: unmappedParameterValue.uuid,
                            text: unmappedParameterValue.text,
                            rgbHex: null,
                        });
                    }
                }
                array.push({
                    unit: null,
                    isCollapsed: false,
                    parameterUuid: parameter.uuid,
                    parameterName: parameter.name,
                    maximalValue: null,
                    minimalValue: null,
                    selectedValue: 'selectedValue' in parameter ? parameter.selectedValue : null,
                    values: mappedParameterValues,
                });
            }

            return array;
        }, []) ?? [];

    const filterShouldBeUsed = checkedFlags.length > 0 || checkedParameters.length > 0;

    if (filterShouldBeUsed) {
        return getActualUrlQueryWithoutDefaultPriceFilter(
            [],
            checkedFlags,
            null,
            null,
            false,
            checkedParameters,
            productFilterOptions,
        );
    }

    return null;
};
