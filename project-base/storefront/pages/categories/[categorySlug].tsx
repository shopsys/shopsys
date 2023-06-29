import { DEFAULT_PAGE_SIZE } from 'components/Blocks/Pagination/Pagination';
import { getEndCursor } from 'components/Blocks/Product/Filter/helpers/getEndCursor';
import { Breadcrumbs } from 'components/Layout/Breadcrumbs/Breadcrumbs';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { CategoryDetailContent } from 'components/Pages/CategoryDetail/CategoryDetailContent';
import { CategoryDetailPageSkeleton } from 'components/Pages/CategoryDetail/CategoryDetailPageSkeleton';
import {
    useCategoryDetailQueryApi,
    CategoryDetailQueryApi,
    CategoryDetailQueryVariablesApi,
    CategoryDetailQueryDocumentApi,
    CategoryProductsQueryDocumentApi,
} from 'graphql/generated';
import { getDomainConfig } from 'helpers/domain/domain';
import { getFilterOptions } from 'helpers/filterOptions/getFilterOptions';
import { mapParametersFilter } from 'helpers/filterOptions/mapParametersFilter';
import { parseFilterOptionsFromQuery } from 'helpers/filterOptions/parseFilterOptionsFromQuery';
import { getDefaultFilterFromFilterOptions } from 'helpers/filterOptions/seoCategories';
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
import { useQueryError } from 'hooks/graphQl/useQueryError';
import { useGtmPageViewEvent } from 'hooks/gtm/useGtmPageViewEvent';
import { useSeoTitleWithPagination } from 'hooks/seo/useSeoTitleWithPagination';
import { NextPage } from 'next';
import { useRouter } from 'next/router';
import { useEffect } from 'react';
import { useSessionStore } from 'store/zustand/useSessionStore';
import { OperationResult, ssrExchange } from 'urql';
import { getSlugFromServerSideUrl, getSlugFromUrl } from 'utils/getSlugFromUrl';

const CategoryDetailPage: NextPage = () => {
    const router = useRouter();
    const slug = getUrlWithoutGetParameters(router.asPath);
    const setDefaultProductFiltersMap = useSessionStore((s) => s.setDefaultProductFiltersMap);
    const orderingMode = getProductListSort(parseProductListSortFromQuery(router.query[SORT_QUERY_PARAMETER_NAME]));

    const filter = mapParametersFilter(
        getFilterOptions(parseFilterOptionsFromQuery(router.query[FILTER_QUERY_PARAMETER_NAME])),
    );

    const [{ data: categoryData, fetching }] = useQueryError(
        useCategoryDetailQueryApi({
            variables: {
                urlSlug: getSlugFromUrl(slug),
                orderingMode,
                filter,
            },
        }),
    );

    useEffect(() => {
        setDefaultProductFiltersMap(
            getDefaultFilterFromFilterOptions(
                categoryData?.category?.products.productFilterOptions,
                categoryData?.category?.products.defaultOrderingMode,
            ),
        );

        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [categoryData?.category?.products.productFilterOptions, categoryData?.category?.products.defaultOrderingMode]);

    const seoTitle = useSeoTitleWithPagination(
        categoryData?.category?.products.totalCount,
        categoryData?.category?.name,
        categoryData?.category?.seoTitle,
    );

    const pageViewEvent = useGtmFriendlyPageViewEvent(categoryData?.category);
    useGtmPageViewEvent(pageViewEvent, fetching);

    return (
        <CommonLayout title={seoTitle} description={categoryData?.category?.seoMetaDescription}>
            <Webline>
                {!!categoryData?.category?.breadcrumb && (
                    <Webline>
                        <Breadcrumbs type="category" key="breadcrumb" breadcrumb={categoryData.category.breadcrumb} />
                    </Webline>
                )}
                {!filter && fetching ? (
                    <CategoryDetailPageSkeleton />
                ) : (
                    !!categoryData?.category && <CategoryDetailContent category={categoryData.category} />
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
        const categoryDetailResponse: OperationResult<CategoryDetailQueryApi, CategoryDetailQueryVariablesApi> =
            await client!
                .query(CategoryDetailQueryDocumentApi, {
                    urlSlug,
                    filter: mapParametersFilter(optionsFilter),
                    orderingMode,
                })
                .toPromise();

        await client!
            .query(CategoryProductsQueryDocumentApi, {
                endCursor: getEndCursor(page),
                orderingMode,
                filter: mapParametersFilter(optionsFilter),
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
