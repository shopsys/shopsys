import { getActualUrlQueryWithoutDefaultPriceFilter } from './GetActualUrlQueryWithoutDefaultPriceFilter';
import { FilterFormFlagType, FilterFormParameterType, FilterOptionsType } from 'types/productFilter';

export const getFilterUrlQueryForSortingInSeoCategory = (productFilterOptions: FilterOptionsType): string | null => {
    const checkedFlags: FilterFormFlagType[] = productFilterOptions.flags.reduce(
        (array: FilterFormFlagType[], flag) => {
            if (flag.isSelected) {
                array.push({ name: flag.flag.name, uuid: flag.flag.uuid, checked: true });
            }
            return array;
        },
        [],
    );
    const checkedParameters: FilterFormParameterType[] =
        productFilterOptions.parameters?.reduce((array: FilterFormParameterType[], parameter) => {
            if (
                ('values' in parameter && parameter.values.filter((value) => value.isSelected).length > 0) ||
                ('selectedValue' in parameter && parameter.selectedValue !== null)
            ) {
                array.push({
                    unit: null,
                    isCollapsed: false,
                    parameterUuid: parameter.uuid,
                    parameterName: parameter.name,
                    maximalValue: null,
                    minimalValue: null,
                    selectedValue: 'selectedValue' in parameter ? parameter.selectedValue : null,
                    values:
                        'values' in parameter
                            ? parameter.values
                                  .filter((value) => value.isSelected)
                                  .map((value) => ({
                                      checked: value.isSelected,
                                      uuid: value.uuid,
                                      text: value.text,
                                      rgbHex: null,
                                  }))
                            : [],
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
