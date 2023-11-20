import {
    ProductOrderingModeEnumApi,
    Maybe,
    ProductFilterApi,
    CategoryDetailFragmentApi,
    CategoryDetailQueryApi,
    CategoryDetailQueryDocumentApi,
    CategoryDetailQueryVariablesApi,
} from 'graphql/generated';
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
import { useState, useEffect } from 'react';
import { useSessionStore } from 'store/useSessionStore';
import { FilterOptionsUrlQueryType } from 'types/productFilter';
import { useClient, Client } from 'urql';

export const useCategoryDetailData = (
    filter: FilterOptionsUrlQueryType | null,
): [CategoryDetailFragmentApi | undefined, boolean] => {
    const [fetching, setFetching] = useState(false);
    const router = useRouter();
    const urlSlug = getSlugFromUrl(router.asPath);
    const [lastFetchedUrlSlug, setLastFetchedUrlSlug] = useState(urlSlug);
    const client = useClient();
    const mappedProductFilter = mapParametersFilter(filter);
    const { sort } = useQueryParams();
    const wasRedirectedToSeoCategory = useSessionStore((s) => s.wasRedirectedToSeoCategory);
    const [categoryDetailData, setCategoryDetailData] = useState(
        readCategoryDetailFromCache(client, urlSlug, sort, mappedProductFilter),
    );
    const setOriginalCategorySlug = useSessionStore((s) => s.setOriginalCategorySlug);
    const setWasRedirectedToSeoCategory = useSessionStore((s) => s.setWasRedirectedToSeoCategory);
    const hasFetchedForCurrentSlug = !fetching && lastFetchedUrlSlug === urlSlug;

    useEffect(() => {
        if (wasRedirectedToSeoCategory) {
            return;
        }
        setFetching(true);

        client
            .query<CategoryDetailQueryApi, CategoryDetailQueryVariablesApi>(CategoryDetailQueryDocumentApi, {
                urlSlug,
                orderingMode: sort,
                filter: mappedProductFilter,
            })
            .toPromise()
            .then((response) => {
                setCategoryDetailData(response.data?.category ?? undefined);
                handleSeoCategorySlugUpdate(
                    router,
                    urlSlug,
                    response.data?.category?.originalCategorySlug,
                    response.data?.category?.slug,
                    filter,
                    sort,
                    setWasRedirectedToSeoCategory,
                    setOriginalCategorySlug,
                );
            })
            .finally(() => {
                setFetching(false);
                setLastFetchedUrlSlug(urlSlug);
            });
    }, [urlSlug, sort, JSON.stringify(filter)]);

    return [categoryDetailData, !hasFetchedForCurrentSlug];
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

const readCategoryDetailFromCache = (
    client: Client,
    urlSlug: string,
    orderingMode: ProductOrderingModeEnumApi | null,
    filter: Maybe<ProductFilterApi>,
) =>
    client.readQuery<CategoryDetailQueryApi, CategoryDetailQueryVariablesApi>(CategoryDetailQueryDocumentApi, {
        urlSlug,
        orderingMode,
        filter,
    })?.data?.category ?? undefined;
