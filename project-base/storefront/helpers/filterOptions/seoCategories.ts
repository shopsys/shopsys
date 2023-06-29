import {
    ListedProductConnectionPreviewFragmentApi,
    CategoryDetailFragmentApi,
    ProductFilterOptionsFragmentApi,
    ProductOrderingModeEnumApi,
} from 'graphql/generated';
import router from 'next/router';
import { useEffect } from 'react';
import { DefaultProductFiltersMapType } from 'store/zustand/slices/createSeoCategorySlice';
import { useSessionStore } from 'store/zustand/useSessionStore';
import { FilterOptionsUrlQueryType } from 'types/productFilter';

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

export const getChangedDefaultFiltersAfterFlagChange = (
    defaultProductFiltersMap: DefaultProductFiltersMapType,
    filter: FilterOptionsUrlQueryType,
    changedFlagUuid: string,
): FilterOptionsUrlQueryType => {
    if (!defaultProductFiltersMap.flags.delete(changedFlagUuid)) {
        defaultProductFiltersMap.flags.add(changedFlagUuid);
    }

    return getChangedDefaultFilters(defaultProductFiltersMap, filter);
};

export const getChangedDefaultFiltersAfterParameterChange = (
    defaultProductFiltersMap: DefaultProductFiltersMapType,
    filter: FilterOptionsUrlQueryType,
    changedaParameterUuid: string,
    changedaParameterValueUuid: string,
): FilterOptionsUrlQueryType => {
    const matchingDefaultParameter = defaultProductFiltersMap.parameters.get(changedaParameterUuid);
    if (matchingDefaultParameter) {
        if (!matchingDefaultParameter.delete(changedaParameterValueUuid)) {
            matchingDefaultParameter.add(changedaParameterValueUuid);
        }
    } else {
        defaultProductFiltersMap.parameters.set(changedaParameterUuid, new Set([changedaParameterValueUuid]));
    }

    return getChangedDefaultFilters(defaultProductFiltersMap, filter);
};

export const getChangedDefaultFiltersAfterMinimumPriceChange = (
    defaultProductFiltersMap: DefaultProductFiltersMapType,
    filter: FilterOptionsUrlQueryType,
    newMinPrice: number | undefined,
): FilterOptionsUrlQueryType => {
    return getChangedDefaultFilters(defaultProductFiltersMap, { ...filter, minimalPrice: newMinPrice });
};

export const getChangedDefaultFiltersAfterMaximumPriceChange = (
    defaultProductFiltersMap: DefaultProductFiltersMapType,
    filter: FilterOptionsUrlQueryType,
    newMaxPrice: number | undefined,
): FilterOptionsUrlQueryType => {
    return getChangedDefaultFilters(defaultProductFiltersMap, { ...filter, maximalPrice: newMaxPrice });
};

export const getChangedDefaultFilters = (
    defaultProductFiltersMap: DefaultProductFiltersMapType,
    filter: FilterOptionsUrlQueryType,
) => ({
    ...filter,
    flags: Array.from(defaultProductFiltersMap.flags),
    parameters: Array.from(defaultProductFiltersMap.parameters)
        .map(([defaultParameterUuid, defaultParameterValues]) => ({
            parameter: defaultParameterUuid,
            values: Array.from(defaultParameterValues),
        }))
        .filter(({ values }) => values.length !== 0),
});

export const useHandleDefaultFiltersUpdate = (
    productsPreview: ListedProductConnectionPreviewFragmentApi | undefined,
) => {
    const setDefaultProductFiltersMap = useSessionStore((s) => s.setDefaultProductFiltersMap);

    useEffect(() => {
        setDefaultProductFiltersMap(
            getDefaultFilterFromFilterOptions(
                productsPreview?.productFilterOptions,
                productsPreview?.defaultOrderingMode,
            ),
        );

        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [productsPreview?.productFilterOptions, productsPreview?.defaultOrderingMode]);
};

export const useHandleSeoCategorySlugUpdate = (category: CategoryDetailFragmentApi | undefined | null) => {
    const setOriginalCategorySlug = useSessionStore((s) => s.setOriginalCategorySlug);

    useEffect(() => {
        if (category?.originalCategorySlug) {
            router.replace(category.slug, undefined, { shallow: true });
        }
        setOriginalCategorySlug(category?.originalCategorySlug ?? undefined);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [category?.originalCategorySlug, category?.slug]);
};
