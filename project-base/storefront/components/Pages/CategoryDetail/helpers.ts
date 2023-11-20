import { ProductOrderingModeEnumApi, CategoryDetailFragmentApi, useCategoryDetailQueryApi } from 'graphql/generated';
import { buildNewQueryAfterFilterChange } from 'helpers/filterOptions/buildNewQueryAfterFilterChange';
import { getFilterWithoutEmpty } from 'helpers/filterOptions/getFilterWithoutEmpty';
import { mapParametersFilter } from 'helpers/filterOptions/mapParametersFilter';
import { getFilterWithoutSeoSensitiveFilters } from 'helpers/filterOptions/seoCategories';
import { getStringWithoutLeadingSlash } from 'helpers/parsing/stringWIthoutSlash';
import {
    getSlugFromUrl,
    getUrlQueriesWithoutFalsyValues,
    getUrlQueriesWithoutDynamicPageQueries,
} from 'helpers/parsing/urlParsing';
import { useQueryParams } from 'hooks/useQueryParams';
import { NextRouter, useRouter } from 'next/router';
import { useEffect, useRef } from 'react';
import { useSessionStore } from 'store/useSessionStore';
import { FilterOptionsUrlQueryType } from 'types/productFilter';

export const useCategoryDetailData = (
    filter: FilterOptionsUrlQueryType | null,
): { categoryData: CategoryDetailFragmentApi | null | undefined; isFetchingVisible: boolean } => {
    const router = useRouter();
    const urlSlug = getSlugFromUrl(router.asPath);
    const { sort } = useQueryParams();
    const mappedProductFilter = mapParametersFilter(filter);

    const lastUsedUrlRef = useRef<string>();
    const lastSeoCategoryRedirectRef = useRef<string>();

    const setOriginalCategorySlug = useSessionStore((s) => s.setOriginalCategorySlug);
    const wasRedirectedFromSeoCategory = useSessionStore((s) => s.wasRedirectedFromSeoCategory);
    const setWasRedirectedFromSeoCategory = useSessionStore((s) => s.setWasRedirectedFromSeoCategory);
    const wasRedirectedToSeoCategory = useSessionStore((s) => s.wasRedirectedToSeoCategory);
    const setWasRedirectedToSeoCategory = useSessionStore((s) => s.setWasRedirectedToSeoCategory);
    const isInSeoRedirectedCategory = lastSeoCategoryRedirectRef.current === urlSlug;

    const [{ data: categoryDetailData, fetching }] = useCategoryDetailQueryApi({
        variables: {
            urlSlug,
            orderingMode: sort,
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
            sort,
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
    currentSort: ProductOrderingModeEnumApi | null,
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
