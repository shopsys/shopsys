import { DEFAULT_PAGE_SIZE } from 'components/Blocks/Pagination/Pagination';
import { getEndCursor } from 'components/Blocks/Product/Filter/helpers/getEndCursor';
import { Breadcrumbs } from 'components/Layout/Breadcrumbs/Breadcrumbs';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { CategoryDetailContent } from 'components/Pages/CategoryDetail/CategoryDetailContent';
import { CategoryDetailPageSkeleton } from 'components/Pages/CategoryDetail/CategoryDetailPageSkeleton';
import {
    CategoryDetailQueryApi,
    CategoryDetailQueryVariablesApi,
    CategoryDetailQueryDocumentApi,
    CategoryProductsQueryDocumentApi,
    ProductFilterApi,
    CategoryDetailFragmentApi,
    Maybe,
    ProductOrderingModeEnumApi,
} from 'graphql/generated';

import { getFilterOptions } from 'helpers/filterOptions/getFilterOptions';
import { mapParametersFilter } from 'helpers/filterOptions/mapParametersFilter';
import { parseFilterOptionsFromQuery } from 'helpers/filterOptions/parseFilterOptionsFromQuery';
import { useHandleDefaultFiltersUpdate } from 'helpers/filterOptions/seoCategories';
import { useGtmFriendlyPageViewEvent } from 'helpers/gtm/eventFactories';
import { getServerSidePropsWrapper } from 'helpers/misc/getServerSidePropsWrapper';
import { initServerSideProps } from 'helpers/misc/initServerSideProps';
import { isRedirectedFromSsr } from 'helpers/misc/isServer';
import { parsePageNumberFromQuery } from 'helpers/pagination/parsePageNumberFromQuery';
import { getUrlWithoutGetParameters } from 'helpers/parsing/getUrlWithoutGetParameters';
import { getStringWithoutLeadingSlash } from 'helpers/parsing/stringWIthoutSlash';
import {
    PAGE_QUERY_PARAMETER_NAME,
    SORT_QUERY_PARAMETER_NAME,
    FILTER_QUERY_PARAMETER_NAME,
} from 'helpers/queryParams/queryParamNames';
import { getProductListSort } from 'helpers/sorting/getProductListSort';
import { parseProductListSortFromQuery } from 'helpers/sorting/parseProductListSortFromQuery';
import { createClient } from 'helpers/urql/createClient';
import { useGtmPageViewEvent } from 'hooks/gtm/useGtmPageViewEvent';
import { useSeoTitleWithPagination } from 'hooks/seo/useSeoTitleWithPagination';
import { useQueryParams } from 'hooks/useQueryParams';
import { NextPage } from 'next';
import { NextRouter, useRouter } from 'next/router';
import { useEffect, useState } from 'react';
import { useSessionStore } from 'store/zustand/useSessionStore';
import { Client, useClient } from 'urql';
import { getSlugFromServerSideUrl, getSlugFromUrl } from 'utils/getSlugFromUrl';

const CategoryDetailPage: NextPage = () => {
    const originalCategorySlug = useSessionStore((s) => s.originalCategorySlug);
    const { sort, filter } = useQueryParams();
    const [categoryData, fetching] = useCategoryDetailData(mapParametersFilter(filter));

    useHandleDefaultFiltersUpdate(categoryData?.products);

    const seoTitle = useSeoTitleWithPagination(
        categoryData?.products.totalCount,
        categoryData?.name,
        categoryData?.seoTitle,
    );

    const pageViewEvent = useGtmFriendlyPageViewEvent(categoryData);
    useGtmPageViewEvent(pageViewEvent, fetching);

    const isSkeletonVisible = !filter && !originalCategorySlug && !sort && fetching;

    return (
        <CommonLayout title={seoTitle} description={categoryData?.seoMetaDescription}>
            {!!categoryData?.breadcrumb && (
                <Webline>
                    <Breadcrumbs type="category" key="breadcrumb" breadcrumb={categoryData.breadcrumb} />
                </Webline>
            )}
            {isSkeletonVisible ? (
                <CategoryDetailPageSkeleton />
            ) : (
                !!categoryData && <CategoryDetailContent category={categoryData} />
            )}
        </CommonLayout>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, ssrExchange, t }) =>
        async (context) => {
            const client = await createClient({
                publicGraphqlEndpoint: domainConfig.publicGraphqlEndpoint,
                ssrExchange,
                redisClient,
                context,
                t,
            });

            const orderingMode = getProductListSort(
                parseProductListSortFromQuery(context.query[SORT_QUERY_PARAMETER_NAME]),
            );
            const optionsFilter = getFilterOptions(
                parseFilterOptionsFromQuery(context.query[FILTER_QUERY_PARAMETER_NAME]),
            );
            const page = parsePageNumberFromQuery(context.query[PAGE_QUERY_PARAMETER_NAME]);

            if (isRedirectedFromSsr(context.req.headers)) {
                const urlSlug = getSlugFromServerSideUrl(context.req.url ?? '');
                const filter = mapParametersFilter(optionsFilter);
                const categoryDetailResponsePromise = client!
                    .query<CategoryDetailQueryApi, CategoryDetailQueryVariablesApi>(CategoryDetailQueryDocumentApi, {
                        urlSlug,
                        filter,
                        orderingMode,
                    })
                    .toPromise();

                const categoryProductsResponsePromise = client!
                    .query(CategoryProductsQueryDocumentApi, {
                        endCursor: getEndCursor(page),
                        orderingMode,
                        filter,
                        urlSlug,
                        pageSize: DEFAULT_PAGE_SIZE,
                    })
                    .toPromise();

                const [categoryDetailResponse] = await Promise.all([
                    categoryDetailResponsePromise,
                    categoryProductsResponsePromise,
                ]);

                if (!categoryDetailResponse.data?.category && !(context.res.statusCode === 503)) {
                    return {
                        notFound: true,
                    };
                }
            }

            const initServerSideData = await initServerSideProps({
                domainConfig,
                context,
                client,
                ssrExchange,
            });

            return initServerSideData;
        },
);

export default CategoryDetailPage;

const useCategoryDetailData = (filter: ProductFilterApi | null): [undefined | CategoryDetailFragmentApi, boolean] => {
    const client = useClient();
    const router = useRouter();
    const urlSlug = getSlugFromUrl(getUrlWithoutGetParameters(router.asPath));
    const { sort } = useQueryParams();
    const wasRedirectedToSeoCategory = useSessionStore((s) => s.wasRedirectedToSeoCategory);
    const [categoryDetailData, setCategoryDetailData] = useState<undefined | CategoryDetailFragmentApi>(
        readCategoryDetailFromCache(client, urlSlug, sort, filter),
    );
    const setOriginalCategorySlug = useSessionStore((s) => s.setOriginalCategorySlug);
    const setWasRedirectedToSeoCategory = useSessionStore((s) => s.setWasRedirectedToSeoCategory);

    const [fetching, setFetching] = useState<boolean>(false);

    useEffect(() => {
        if (wasRedirectedToSeoCategory) {
            return;
        }
        setFetching(true);

        client
            .query<CategoryDetailQueryApi, CategoryDetailQueryVariablesApi>(CategoryDetailQueryDocumentApi, {
                urlSlug,
                orderingMode: sort ?? null,
                filter,
            })
            .toPromise()
            .then((response) => {
                setCategoryDetailData(response.data?.category ?? undefined);
                handleSeoCategorySlugUpdate(
                    router,
                    urlSlug,
                    response.data?.category?.originalCategorySlug,
                    response.data?.category?.slug,
                    setWasRedirectedToSeoCategory,
                    setOriginalCategorySlug,
                );
            })
            .finally(() => setFetching(false));
    }, [urlSlug, sort, JSON.stringify(filter)]);

    return [categoryDetailData, fetching];
};

export const handleSeoCategorySlugUpdate = (
    router: NextRouter,
    urlSlug: string,
    originalCategorySlug: string | undefined | null,
    categorySlug: string | undefined,
    setWasRedirectedToSeoCategory: (value: boolean) => void,
    setOriginalCategorySlug: (value: string | undefined) => void,
) => {
    const isCurrentAndRedirectSlugDifferent = getStringWithoutLeadingSlash(categorySlug ?? '') !== urlSlug;

    if (originalCategorySlug && isCurrentAndRedirectSlugDifferent && categorySlug) {
        setWasRedirectedToSeoCategory(true);
        router.replace(
            { pathname: '/categories/[categorySlug]', query: { categorySlug: '/televize-audio-nejlevnejsi' } },
            { pathname: categorySlug },
            {
                shallow: true,
            },
        );
    }

    setOriginalCategorySlug(originalCategorySlug ?? undefined);
};

const readCategoryDetailFromCache = (
    client: Client,
    urlSlug: string,
    sort: ProductOrderingModeEnumApi | null,
    filter: Maybe<ProductFilterApi>,
) => {
    return (
        client.readQuery<CategoryDetailQueryApi, CategoryDetailQueryVariablesApi>(CategoryDetailQueryDocumentApi, {
            urlSlug,
            orderingMode: sort,
            filter,
        })?.data?.category ?? undefined
    );
};
