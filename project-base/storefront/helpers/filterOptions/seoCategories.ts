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
import { FilterOptionsParameterUrlQueryType, FilterOptionsUrlQueryType } from 'types/productFilter';

export const DEFAULT_SORT = ProductOrderingModeEnumApi.PriorityApi as const;

export const getEmptyDefaultProductFiltersMap = (): DefaultProductFiltersMapType => ({
    flags: new Set(),
    sort: DEFAULT_SORT,
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
    filter: FilterOptionsUrlQueryType | null,
    changedFlagUuid: string,
): FilterOptionsUrlQueryType => {
    if (!defaultProductFiltersMap.flags.delete(changedFlagUuid)) {
        defaultProductFiltersMap.flags.add(changedFlagUuid);
    }

    return getChangedDefaultFilters(defaultProductFiltersMap, filter);
};

export const getChangedDefaultFiltersAfterParameterChange = (
    defaultProductFiltersMap: DefaultProductFiltersMapType,
    filter: FilterOptionsUrlQueryType | null,
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

export const getChangedDefaultFiltersAfterSliderParameterChange = (
    defaultProductFiltersMap: DefaultProductFiltersMapType,
    filter: FilterOptionsUrlQueryType,
    changedaParameterUuid: string,
    minimalValue: number | undefined,
    maximalValue: number | undefined,
): FilterOptionsUrlQueryType => {
    const selectedParameters: FilterOptionsParameterUrlQueryType[] = Array.from(defaultProductFiltersMap.parameters)
        .map(([defaultParameterUuid, defaultParameterValues]) => ({
            parameter: defaultParameterUuid,
            values: Array.from(defaultParameterValues),
        }))
        .filter(({ values }) => values.length !== 0);

    selectedParameters.push({
        parameter: changedaParameterUuid,
        minimalValue,
        maximalValue,
    });

    return {
        ...filter,
        flags: Array.from(defaultProductFiltersMap.flags),
        parameters: selectedParameters,
    };
};

export const getChangedDefaultFiltersAfterMinimumPriceChange = (
    defaultProductFiltersMap: DefaultProductFiltersMapType,
    filter: FilterOptionsUrlQueryType | null,
    newMinPrice: number | undefined,
): FilterOptionsUrlQueryType => {
    return getChangedDefaultFilters(defaultProductFiltersMap, { ...filter, minimalPrice: newMinPrice });
};

export const getChangedDefaultFiltersAfterMaximumPriceChange = (
    defaultProductFiltersMap: DefaultProductFiltersMapType,
    filter: FilterOptionsUrlQueryType | null,
    newMaxPrice: number | undefined,
): FilterOptionsUrlQueryType => {
    return getChangedDefaultFilters(defaultProductFiltersMap, { ...filter, maximalPrice: newMaxPrice });
};

export const getChangedDefaultFilters = (
    updatedProductFiltersMap: DefaultProductFiltersMapType,
    filter: FilterOptionsUrlQueryType | null,
) => ({
    ...filter,
    flags: Array.from(updatedProductFiltersMap.flags),
    parameters: Array.from(updatedProductFiltersMap.parameters)
        .map(([updatedParameterUuid, updatedParameterValues]) => ({
            parameter: updatedParameterUuid,
            values: Array.from(updatedParameterValues),
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
    }, [productsPreview?.productFilterOptions, productsPreview?.defaultOrderingMode]);
};

export const useHandleSeoCategorySlugUpdate = (category: CategoryDetailFragmentApi | undefined | null) => {
    const setOriginalCategorySlug = useSessionStore((s) => s.setOriginalCategorySlug);

    useEffect(() => {
        const isCurrentAndRedirectSlugDifferent = getStringWithoutLeadingSlash(category?.slug ?? '') !== urlSlug;

        if (category?.originalCategorySlug && isCurrentAndRedirectSlugDifferent) {
            setWasRedirectedToSeoCategory(true);
            router.replace(category.slug, undefined, { shallow: true });
        }

        setOriginalCategorySlug(category?.originalCategorySlug ?? undefined);
    }, [category?.originalCategorySlug, category?.slug]);
};
