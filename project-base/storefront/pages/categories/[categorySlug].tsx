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
} from 'graphql/generated';
import { getDomainConfig } from 'helpers/domain/domain';
import { getFilterOptions } from 'helpers/filterOptions/getFilterOptions';
import { mapParametersFilter } from 'helpers/filterOptions/mapParametersFilter';
import { parseFilterOptionsFromQuery } from 'helpers/filterOptions/parseFilterOptionsFromQuery';
import { useHandleDefaultFiltersUpdate, useHandleSeoCategorySlugUpdate } from 'helpers/filterOptions/seoCategories';
import { useGtmFriendlyPageViewEvent } from 'helpers/gtm/eventFactories';
import { getServerSidePropsWithRedisClient } from 'helpers/misc/getServerSidePropsWithRedisClient';
import { initServerSideProps } from 'helpers/misc/initServerSideProps';
import { isRedirectedFromSsr } from 'helpers/misc/isServer';
import { parsePageNumberFromQuery } from 'helpers/pagination/parsePageNumberFromQuery';
import { getUrlWithoutGetParameters } from 'helpers/parsing/getUrlWithoutGetParameters';
import {
    PAGE_QUERY_PARAMETER_NAME,
    SORT_QUERY_PARAMETER_NAME,
    FILTER_QUERY_PARAMETER_NAME,
} from 'helpers/queryParams/queryParamNames';
import { getProductListSort } from 'helpers/sorting/getProductListSort';
import { parseProductListSortFromQuery } from 'helpers/sorting/parseProductListSortFromQuery';
import { createClient } from 'helpers/urql/createClient';
import { handleQueryError } from 'hooks/graphQl/useQueryError';
import { useGtmPageViewEvent } from 'hooks/gtm/useGtmPageViewEvent';
import { useSeoTitleWithPagination } from 'hooks/seo/useSeoTitleWithPagination';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { NextPage } from 'next';
import { useRouter } from 'next/router';
import { useEffect, useState } from 'react';
import { useSessionStore } from 'store/zustand/useSessionStore';
import { OperationResult, ssrExchange, useClient } from 'urql';
import { getSlugFromServerSideUrl, getSlugFromUrl } from 'utils/getSlugFromUrl';

const CategoryDetailPage: NextPage = () => {
    const router = useRouter();
    const orderingMode = getProductListSort(parseProductListSortFromQuery(router.query[SORT_QUERY_PARAMETER_NAME]));
    const originalCategorySlug = useSessionStore((s) => s.originalCategorySlug);
    const filter = mapParametersFilter(
        getFilterOptions(parseFilterOptionsFromQuery(router.query[FILTER_QUERY_PARAMETER_NAME])),
    );
    const [categoryData, fetching] = useCategoryDetailData(filter);

    useHandleDefaultFiltersUpdate(categoryData?.products);
    useHandleSeoCategorySlugUpdate(categoryData);

    const seoTitle = useSeoTitleWithPagination(
        categoryData?.products.totalCount,
        categoryData?.name,
        categoryData?.seoTitle,
    );

    const pageViewEvent = useGtmFriendlyPageViewEvent(categoryData);
    useGtmPageViewEvent(pageViewEvent, fetching);

    const isSkeletonVisible = !filter && !originalCategorySlug && !orderingMode && fetching;

    return (
        <CommonLayout title={seoTitle} description={categoryData?.seoMetaDescription}>
            <Webline>
                {!!categoryData?.breadcrumb && (
                    <Webline>
                        <Breadcrumbs type="category" key="breadcrumb" breadcrumb={categoryData.breadcrumb} />
                    </Webline>
                )}
                {isSkeletonVisible ? (
                    <CategoryDetailPageSkeleton />
                ) : (
                    !!categoryData && (
                        <>
                            <CategoryDetailContent
                                showTitleAndDescriptionSkeleton={
                                    (!!filter || !!orderingMode || !!originalCategorySlug) && fetching
                                }
                                category={categoryData}
                            />
                        </>
                    )
                )}
            </Webline>
        </CommonLayout>
    );
};

export const getServerSideProps = getServerSidePropsWithRedisClient((redisClient) => async (context) => {
    const domainConfig = getDomainConfig(context.req.headers.host!);
    const ssrCache = ssrExchange({ isClient: false });
    const client = createClient(context, domainConfig.publicGraphqlEndpoint, ssrCache, redisClient);

    const orderingMode = getProductListSort(parseProductListSortFromQuery(context.query[SORT_QUERY_PARAMETER_NAME]));
    const optionsFilter = getFilterOptions(parseFilterOptionsFromQuery(context.query[FILTER_QUERY_PARAMETER_NAME]));
    const page = parsePageNumberFromQuery(context.query[PAGE_QUERY_PARAMETER_NAME]);

    if (isRedirectedFromSsr(context.req.headers)) {
        const urlSlug = getSlugFromServerSideUrl(context.req.url ?? '');
        const filter = mapParametersFilter(optionsFilter);
        const categoryDetailResponse: OperationResult<CategoryDetailQueryApi, CategoryDetailQueryVariablesApi> =
            await client!
                .query(CategoryDetailQueryDocumentApi, {
                    urlSlug,
                    filter,
                    orderingMode,
                })
                .toPromise();

        await client!
            .query(CategoryProductsQueryDocumentApi, {
                endCursor: getEndCursor(page),
                orderingMode,
                filter,
                urlSlug,
                pageSize: DEFAULT_PAGE_SIZE,
            })
            .toPromise();

        if (!categoryDetailResponse.data?.category && !(context.res.statusCode === 503)) {
            return {
                notFound: true,
            };
        }
    }

    const initServerSideData = await initServerSideProps({
        context,
        client,
        ssrCache,
        redisClient,
    });

    return initServerSideData;
});

export default CategoryDetailPage;

const useCategoryDetailData = (filter: ProductFilterApi | null): [undefined | CategoryDetailFragmentApi, boolean] => {
    const client = useClient();
    const router = useRouter();
    const t = useTypedTranslationFunction();
    const urlSlug = getSlugFromUrl(getUrlWithoutGetParameters(router.asPath));
    const orderingMode = getProductListSort(parseProductListSortFromQuery(router.query[SORT_QUERY_PARAMETER_NAME]));
    const wasRedirectedToSeoCategory = useSessionStore((s) => s.wasRedirectedToSeoCategory);
    const [categoryDetailData, setCategoryDetailData] = useState<undefined | CategoryDetailFragmentApi>(undefined);
    const [fetching, setFetching] = useState<boolean>(false);

    useEffect(() => {
        if (wasRedirectedToSeoCategory) {
            return;
        }
        setFetching(true);

        client
            .query<CategoryDetailQueryApi, CategoryDetailQueryVariablesApi>(CategoryDetailQueryDocumentApi, {
                urlSlug,
                orderingMode,
                filter,
            })
            .toPromise()
            .then((response) => {
                handleQueryError(response.error, t);
                setCategoryDetailData(response.data?.category ?? undefined);
            })
            .finally(() => setFetching(false));
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [urlSlug, orderingMode, JSON.stringify(filter)]);

    return [categoryDetailData, fetching];
};
