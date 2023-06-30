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
    PARAMETERS: {
        CHECKBOX: true,
        SLIDER: false,
    },
};

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
