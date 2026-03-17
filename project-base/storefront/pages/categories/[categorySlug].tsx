import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { getEndCursor } from 'components/Blocks/Product/Filter/utils/getEndCursor';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { PageDefer } from 'components/Layout/PageDefer';
import { CategoryDetailContent } from 'components/Pages/CategoryDetail/CategoryDetailContent';
import {
    useCategoryDetailData,
    useHandleDefaultFiltersUpdate,
} from 'components/Pages/CategoryDetail/categoryDetailUtils';
import { DEFAULT_PAGE_SIZE } from 'config/constants';
import {
    CategoryDetailQueryDocument,
    TypeCategoryDetailQuery,
    TypeCategoryDetailQueryVariables,
} from 'graphql/requests/categories/queries/CategoryDetailQuery.generated';
import { CategoryProductsQueryDocument } from 'graphql/requests/products/queries/CategoryProductsQuery.generated';
import { NextPage } from 'next';
import dynamic from 'next/dynamic';
import { createClient } from 'urql/createClient';
import { handleServerSideErrorResponseForFriendlyUrls } from 'utils/errors/handleServerSideErrorResponseForFriendlyUrls';
import { getMappedProductFilter } from 'utils/filterOptions/getMappedProductFilter';
import { getRedirectWithOffsetPage } from 'utils/loadMore/getRedirectWithOffsetPage';
import { getNumberFromUrlQuery } from 'utils/parsing/getNumberFromUrlQuery';
import { getProductListSortFromUrlQuery } from 'utils/parsing/getProductListSortFromUrlQuery';
import { getSlugFromServerSideUrl } from 'utils/parsing/getSlugFromServerSideUrl';
import {
    FILTER_QUERY_PARAMETER_NAME,
    LOAD_MORE_QUERY_PARAMETER_NAME,
    PAGE_QUERY_PARAMETER_NAME,
    SORT_QUERY_PARAMETER_NAME,
} from 'utils/queryParamNames';
import { useCurrentFilterQuery } from 'utils/queryParams/useCurrentFilterQuery';
import { useCurrentSortQuery } from 'utils/queryParams/useCurrentSortQuery';
import { useSeoTitleWithPagination } from 'utils/seo/useSeoTitleWithPagination';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { buildServerSideProps, prefetchLayoutQueries, ServerSidePropsType } from 'utils/serverSide/initServerSideProps';

const Error404Content = dynamic(
    () => import('components/Pages/ErrorPage/Error404Content').then((m) => m.Error404Content),
    { ssr: false },
);

const CategoryDetailPage: NextPage<ServerSidePropsType> = () => {
    const currentFilter = useCurrentFilterQuery();
    const currentSort = useCurrentSortQuery();
    const { categoryData, isFetchingVisible } = useCategoryDetailData(currentFilter);

    useHandleDefaultFiltersUpdate(categoryData?.products);
    const seoTitle = useSeoTitleWithPagination(
        categoryData?.products.totalCount,
        categoryData?.name,
        categoryData?.seoTitle,
    );

    const firstImageUrl = categoryData?.images[0]?.url;

    if (!categoryData && !isFetchingVisible) {
        return <Error404Content />;
    }

    return (
        <PageDefer>
            {(!!currentFilter || !!currentSort) && <MetaRobots content="noindex, follow" />}

            <CommonLayout
                breadcrumbs={categoryData?.breadcrumb}
                breadcrumbsType="category"
                description={categoryData?.seoMetaDescription}
                hreflangLinks={categoryData?.hreflangLinks}
                isFetchingData={isFetchingVisible}
                ogImageUrlDefault={firstImageUrl}
                title={seoTitle}
            >
                {!!categoryData && (
                    <CategoryDetailContent category={categoryData} isFetchingVisible={isFetchingVisible} />
                )}
            </CommonLayout>
        </PageDefer>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, ssrExchange, t }) =>
        async (context) => {
            const page = getNumberFromUrlQuery(context.query[PAGE_QUERY_PARAMETER_NAME], 1);
            const loadMore = getNumberFromUrlQuery(context.query[LOAD_MORE_QUERY_PARAMETER_NAME], 0);
            const urlSlug = getSlugFromServerSideUrl(context.req.url ?? '', context.req.headers);
            const redirect = getRedirectWithOffsetPage(page, loadMore, urlSlug, context.query);

            if (redirect) {
                return redirect;
            }

            const client = await createClient({
                domainConfig,
                ssrExchange,
                redisClient,
                context,
                t,
            });

            const filter = getMappedProductFilter(context.query[FILTER_QUERY_PARAMETER_NAME]);
            const orderingMode = getProductListSortFromUrlQuery(context.query[SORT_QUERY_PARAMETER_NAME]);
            const categoryDetailResponsePromise = client
                .query<TypeCategoryDetailQuery, TypeCategoryDetailQueryVariables>(CategoryDetailQueryDocument, {
                    urlSlug,
                    filter,
                    orderingMode,
                })
                .toPromise();

            const categoryProductsResponsePromise = client
                .query(CategoryProductsQueryDocument, {
                    endCursor: getEndCursor(page),
                    orderingMode,
                    filter,
                    urlSlug,
                    pageSize: DEFAULT_PAGE_SIZE * (loadMore + 1),
                })
                .toPromise();

            const [categoryDetailResponse, categoryProductsResponse, layoutResult] = await Promise.all([
                categoryDetailResponsePromise,
                categoryProductsResponsePromise,
                prefetchLayoutQueries({ client, context, domainConfig }),
            ]);

            const serverSideCategoryDetailErrorResponse = handleServerSideErrorResponseForFriendlyUrls(
                categoryDetailResponse.error,
                categoryDetailResponse.data?.category,
                context,
                domainConfig.url,
                urlSlug,
            );

            if (serverSideCategoryDetailErrorResponse) {
                return serverSideCategoryDetailErrorResponse;
            }

            const serverSideCategoryProductsErrorResponse = handleServerSideErrorResponseForFriendlyUrls(
                categoryProductsResponse.error,
                categoryProductsResponse.data?.products,
                context,
                domainConfig.url,
                urlSlug,
            );

            if (serverSideCategoryProductsErrorResponse) {
                return serverSideCategoryProductsErrorResponse;
            }

            return buildServerSideProps({
                layoutResult,
                client,
                ssrExchange,
                context,
                domainConfig,
                pageQueryResults: [categoryDetailResponse, categoryProductsResponse],
            });
        },
);

export default CategoryDetailPage;
