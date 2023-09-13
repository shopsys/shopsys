import {
    ListedProductConnectionPreviewFragmentApi,
    ProductFilterOptionsFragmentApi,
    ProductOrderingModeEnumApi,
} from 'graphql/generated';
import { useEffect } from 'react';
import { DefaultProductFiltersMapType } from 'store/slices/createSeoCategorySlice';
import { useSessionStore } from 'store/useSessionStore';
import { FilterOptionsParameterUrlQueryType, FilterOptionsUrlQueryType } from 'types/productFilter';

export const DEFAULT_SORT = ProductOrderingModeEnumApi.PriorityApi as const;

/**
 * For those that are set to "true", we optimistically navigate out from a SEO category when a value of that type is changed
 * This setting needs to mirror the API functionality in the following way
 * - if a filter type "blocks" SEO category on API, it needs to be set as SEO sensitive
 * - if a filter type "allows" SEO category on API, it needs to be set as SEO insensitive
 *
 * @example
 * if the current URL is a SEO category "/my-seo-category" and sorting (which is SEO sensitive)
 * is changed, we navigate right away to "/my-normal-category?sort=NEW_SORTING"
 *
 * if the current URL is a SEO category "/my-seo-category" and availability (which is SEO insensitive)
 * is changed, we stay in the SEO category and navigate to "/my-seo-category?onlyInStock=true"
 */
export const SEO_SENSITIVE_FILTERS = {
    SORT: true,
    AVAILABILITY: false,
    PRICE: false,
    FLAGS: true,
    BRANDS: false,
    PARAMETERS: {
        CHECKBOX: true,
        SLIDER: false,
    },
};

export const getEmptyDefaultProductFiltersMap = (): DefaultProductFiltersMapType => ({
    flags: new Set(),
    brands: new Set(),
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

export const getChangedDefaultFiltersAfterBrandChange = (
    defaultProductFiltersMap: DefaultProductFiltersMapType,
    filter: FilterOptionsUrlQueryType | null,
    changedBrandUuid: string,
): FilterOptionsUrlQueryType => {
    if (!defaultProductFiltersMap.brands.delete(changedBrandUuid)) {
        defaultProductFiltersMap.brands.add(changedBrandUuid);
    }

    return getChangedDefaultFilters(defaultProductFiltersMap, filter);
};

export const getChangedDefaultFiltersAfterParameterChange = (
    defaultProductFiltersMap: DefaultProductFiltersMapType,
    filter: FilterOptionsUrlQueryType | null,
    changedParameterUuid: string,
    changedParameterValueUuid: string,
): FilterOptionsUrlQueryType => {
    const matchingDefaultParameter = defaultProductFiltersMap.parameters.get(changedParameterUuid);
    if (matchingDefaultParameter) {
        if (!matchingDefaultParameter.delete(changedParameterValueUuid)) {
            matchingDefaultParameter.add(changedParameterValueUuid);
        }
    } else {
        defaultProductFiltersMap.parameters.set(changedParameterUuid, new Set([changedParameterValueUuid]));
    }

    return getChangedDefaultFilters(defaultProductFiltersMap, filter);
};

export const getChangedDefaultFiltersAfterSliderParameterChange = (
    defaultProductFiltersMap: DefaultProductFiltersMapType,
    filter: FilterOptionsUrlQueryType | null,
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

export const getChangedDefaultFiltersAfterPriceChange = (
    defaultProductFiltersMap: DefaultProductFiltersMapType,
    filter: FilterOptionsUrlQueryType | null,
    newMinPrice: number | undefined,
    newMaxPrice: number | undefined,
): FilterOptionsUrlQueryType => {
    return getChangedDefaultFilters(defaultProductFiltersMap, {
        ...filter,
        minimalPrice: newMinPrice,
        maximalPrice: newMaxPrice,
    });
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

export const getChangedDefaultFiltersAfterAvailabilityChange = (
    defaultProductFiltersMap: DefaultProductFiltersMapType,
    filter: FilterOptionsUrlQueryType | null,
    isOnlyInStock: boolean,
): FilterOptionsUrlQueryType => {
    return getChangedDefaultFilters(defaultProductFiltersMap, { ...filter, onlyInStock: isOnlyInStock });
};

export const getChangedDefaultFilters = (
    updatedProductFiltersMap: DefaultProductFiltersMapType,
    filter: FilterOptionsUrlQueryType | null,
) => ({
    ...filter,
    brands: Array.from(updatedProductFiltersMap.brands),
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
