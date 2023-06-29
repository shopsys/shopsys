import {
    ProductFilterOptionsFragmentApi,
    ProductOrderingModeEnumApi,
} from 'graphql/generated';
import { DefaultProductFiltersMapType } from 'store/zustand/slices/createSeoCategorySlice';

export const getEmptyDefaultProductFiltersMap = (): DefaultProductFiltersMapType => ({
    flags: new Set(),
    sort: ProductOrderingModeEnumApi.PriorityApi,
    parameters: new Map(),
});

export const getDefaultFilterFromFilterOptions = (
    productFilterOptions: ProductFilterOptionsFragmentApi | undefined,
    defaultOrderingMode: ProductOrderingModeEnumApi | null | undefined,
): DefaultProductFiltersMapType => {
    const defaultProductFiltersMap = getEmptyDefaultProductFiltersMap();

    for (const flagOption of productFilterOptions?.flags || []) {
        if (flagOption.isSelected) {
            defaultProductFiltersMap.flags.add(flagOption.flag.uuid);
        }
    }

    if (defaultOrderingMode) {
        defaultProductFiltersMap.sort = defaultOrderingMode;
    }

    for (const filterOptionParameter of productFilterOptions?.parameters || []) {
        if (!('values' in filterOptionParameter)) {
            continue;
        }

        for (const filterOptionParameterValue of filterOptionParameter.values) {
            if (filterOptionParameterValue.isSelected) {
                const mapValue = defaultProductFiltersMap.parameters.get(filterOptionParameter.uuid) || new Set();
                mapValue.add(filterOptionParameterValue.uuid);
                defaultProductFiltersMap.parameters.set(filterOptionParameter.uuid, mapValue);
            }
        }
    }

    return defaultProductFiltersMap;
};

export const getHasDefaultFilters = (defaultProductFiltersMap: DefaultProductFiltersMapType) =>
    defaultProductFiltersMap.flags.size > 0 || defaultProductFiltersMap.parameters.size > 0;
