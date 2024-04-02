import { CategoryDetailFragment } from 'graphql/requests/categories/fragments/CategoryDetailFragment.generated';
import { useCategoryDetailQuery } from 'graphql/requests/categories/queries/CategoryDetailQuery.generated';
import { ProductFilterOptionsFragment } from 'graphql/requests/productFilterOptions/fragments/ProductFilterOptionsFragment.generated';
import { ListedProductConnectionPreviewFragment } from 'graphql/requests/products/fragments/ListedProductConnectionPreviewFragment.generated';
import { ProductOrderingModeEnum } from 'graphql/types';
import { NextRouter, useRouter } from 'next/router';
import { useEffect, useRef } from 'react';
import { DefaultProductFiltersMapType } from 'store/slices/createSeoCategorySlice';
import { useSessionStore } from 'store/useSessionStore';
import { FilterOptionsUrlQueryType } from 'types/productFilter';
import { buildNewQueryAfterFilterChange } from 'utils/filterOptions/buildNewQueryAfterFilterChange';
import { getFilterWithoutEmpty } from 'utils/filterOptions/getFilterWithoutEmpty';
import { mapParametersFilter } from 'utils/filterOptions/mapParametersFilter';
import { getSlugFromUrl } from 'utils/parsing/getSlugFromUrl';
import { getUrlQueriesWithoutDynamicPageQueries } from 'utils/parsing/getUrlQueriesWithoutDynamicPageQueries';
import { getUrlQueriesWithoutFalsyValues } from 'utils/parsing/getUrlQueriesWithoutFalsyValues';
import { getStringWithoutLeadingSlash } from 'utils/parsing/stringWIthoutSlash';
import { useCurrentSortQuery } from 'utils/queryParams/useCurrentSortQuery';
import { getEmptyDefaultProductFiltersMap } from 'utils/seoCategories/getEmptyDefaultProductFiltersMap';
import { getFilterWithoutSeoSensitiveFilters } from 'utils/seoCategories/getFilterWithoutSeoSensitiveFilters';

export const useCategoryDetailData = (
    filter: FilterOptionsUrlQueryType | null,
): { categoryData: CategoryDetailFragment | null | undefined; isFetchingVisible: boolean } => {
    const router = useRouter();
    const urlSlug = getSlugFromUrl(router.asPath);
    const currentSort = useCurrentSortQuery();
    const mappedProductFilter = mapParametersFilter(filter);

    const lastUsedUrlRef = useRef<string>();
    const lastSeoCategoryRedirectRef = useRef<string>();

    const setOriginalCategorySlug = useSessionStore((s) => s.setOriginalCategorySlug);
    const wasRedirectedFromSeoCategory = useSessionStore((s) => s.wasRedirectedFromSeoCategory);
    const setWasRedirectedFromSeoCategory = useSessionStore((s) => s.setWasRedirectedFromSeoCategory);
    const wasRedirectedToSeoCategory = useSessionStore((s) => s.wasRedirectedToSeoCategory);
    const setWasRedirectedToSeoCategory = useSessionStore((s) => s.setWasRedirectedToSeoCategory);
    const isInSeoRedirectedCategory = lastSeoCategoryRedirectRef.current === urlSlug;

    const [{ data: categoryDetailData, fetching }] = useCategoryDetailQuery({
        variables: {
            urlSlug,
            orderingMode: currentSort,
            filter: mappedProductFilter,
        },
        pause: isInSeoRedirectedCategory,
    });

    const hasFetchedWithCurrentUrl = lastUsedUrlRef.current === urlSlug;
    const isFetchingVisible =
        fetching && !hasFetchedWithCurrentUrl && !wasRedirectedToSeoCategory && !wasRedirectedFromSeoCategory;

    useEffect(() => {
        if (wasRedirectedToSeoCategory) {
            lastSeoCategoryRedirectRef.current = urlSlug;
        }
    }, [urlSlug, wasRedirectedToSeoCategory]);

    useEffect(() => {
        lastUsedUrlRef.current = categoryDetailData?.category ? urlSlug : undefined;
        setWasRedirectedFromSeoCategory(false);
        handleSeoCategorySlugUpdate(
            router,
            urlSlug,
            categoryDetailData?.category?.originalCategorySlug,
            categoryDetailData?.category?.slug,
            filter,
            currentSort,
            setWasRedirectedToSeoCategory,
            setOriginalCategorySlug,
        );
    }, [categoryDetailData]);

    return { categoryData: categoryDetailData?.category, isFetchingVisible };
};

const handleSeoCategorySlugUpdate = (
    router: NextRouter,
    urlSlug: string,
    originalCategorySlug: string | undefined | null,
    categorySlug: string | undefined,
    currentFilter: FilterOptionsUrlQueryType | null,
    currentSort: ProductOrderingModeEnum | null,
    setWasRedirectedToSeoCategory: (value: boolean) => void,
    setOriginalCategorySlug: (value: string | undefined) => void,
) => {
    const isCurrentAndRedirectSlugDifferent = getStringWithoutLeadingSlash(categorySlug ?? '') !== urlSlug;

    if (originalCategorySlug && isCurrentAndRedirectSlugDifferent && categorySlug) {
        const { filteredFilter, filteredSort } = getFilterWithoutSeoSensitiveFilters(currentFilter, currentSort);
        const filterWithoutEmpty = getFilterWithoutEmpty(filteredFilter);
        const newQuery = buildNewQueryAfterFilterChange({}, filterWithoutEmpty, filteredSort);
        const filteredQueries = getUrlQueriesWithoutDynamicPageQueries(getUrlQueriesWithoutFalsyValues(newQuery));

        setWasRedirectedToSeoCategory(true);
        router.replace(
            { pathname: '/categories/[categorySlug]', query: { categorySlug, ...filteredQueries } },
            { pathname: categorySlug, query: filteredQueries },
            { shallow: true },
        );
    }

    setOriginalCategorySlug(originalCategorySlug ?? undefined);
};

export const useHandleDefaultFiltersUpdate = (productsPreview: ListedProductConnectionPreviewFragment | undefined) => {
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

const getDefaultFilterFromFilterOptions = (
    productFilterOptions: ProductFilterOptionsFragment | undefined,
    defaultOrderingMode: ProductOrderingModeEnum | null | undefined,
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
