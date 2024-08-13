import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { getEndCursor } from 'components/Blocks/Product/Filter/utils/getEndCursor';
import { LastVisitedProducts } from 'components/Blocks/Product/LastVisitedProducts/LastVisitedProducts';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { PageDefer } from 'components/Layout/PageDefer';
import { CategoryDetailContent } from 'components/Pages/CategoryDetail/CategoryDetailContent';
import {
    useCategoryDetailData,
    useHandleDefaultFiltersUpdate,
} from 'components/Pages/CategoryDetail/categoryDetailUtils';
import { DEFAULT_PAGE_SIZE } from 'config/constants';
import {
    AdvertsQueryDocument,
    TypeAdvertsQueryVariables,
} from 'graphql/requests/adverts/queries/AdvertsQuery.generated';
import {
    TypeCategoryDetailQuery,
    TypeCategoryDetailQueryVariables,
    CategoryDetailQueryDocument,
} from 'graphql/requests/categories/queries/CategoryDetailQuery.generated';
import { CategoryProductsQueryDocument } from 'graphql/requests/products/queries/CategoryProductsQuery.generated';
import { NextPage } from 'next';
import { createClient } from 'urql/createClient';
import { handleServerSideErrorResponseForFriendlyUrls } from 'utils/errors/handleServerSideErrorResponseForFriendlyUrls';
import { getMappedProductFilter } from 'utils/filterOptions/getMappedProductFilter';
import { getIsRedirectedFromSsr } from 'utils/getIsRedirectedFromSsr';
import { getRedirectWithOffsetPage } from 'utils/loadMore/getRedirectWithOffsetPage';
import { getNumberFromUrlQuery } from 'utils/parsing/getNumberFromUrlQuery';
import { getProductListSortFromUrlQuery } from 'utils/parsing/getProductListSortFromUrlQuery';
import { getSlugFromServerSideUrl } from 'utils/parsing/getSlugFromServerSideUrl';
import {
    PAGE_QUERY_PARAMETER_NAME,
    SORT_QUERY_PARAMETER_NAME,
    FILTER_QUERY_PARAMETER_NAME,
    LOAD_MORE_QUERY_PARAMETER_NAME,
} from 'utils/queryParamNames';
import { useCurrentFilterQuery } from 'utils/queryParams/useCurrentFilterQuery';
import { useCurrentSortQuery } from 'utils/queryParams/useCurrentSortQuery';
import { useSeoTitleWithPagination } from 'utils/seo/useSeoTitleWithPagination';
import { getServerSidePropsWrapper } from 'utils/serverSide/getServerSidePropsWrapper';
import { ServerSidePropsType, initServerSideProps } from 'utils/serverSide/initServerSideProps';

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
                <LastVisitedProducts />
            </CommonLayout>
        </PageDefer>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, ssrExchange, t }) =>
        async (context) => {
            const page = getNumberFromUrlQuery(context.query[PAGE_QUERY_PARAMETER_NAME], 1);
            const loadMore = getNumberFromUrlQuery(context.query[LOAD_MORE_QUERY_PARAMETER_NAME], 0);
            const urlSlug = getSlugFromServerSideUrl(context.req.url ?? '');
            const redirect = getRedirectWithOffsetPage(page, loadMore, urlSlug, context.query);

            if (redirect) {
                return redirect;
            }

            const client = await createClient({
                publicGraphqlEndpoint: domainConfig.publicGraphqlEndpoint,
                ssrExchange,
                redisClient,
                context,
                t,
            });

            let categoryUuid: string | null = null;

            const filter = getMappedProductFilter(context.query[FILTER_QUERY_PARAMETER_NAME]);
            const orderingMode = getProductListSortFromUrlQuery(context.query[SORT_QUERY_PARAMETER_NAME]);
            const categoryDetailResponsePromise = client!
                .query<TypeCategoryDetailQuery, TypeCategoryDetailQueryVariables>(CategoryDetailQueryDocument, {
                    urlSlug,
                    filter,
                    orderingMode,
                })
                .toPromise();

            const categoryProductsResponsePromise = client!
                .query(CategoryProductsQueryDocument, {
                    endCursor: getEndCursor(page),
                    orderingMode,
                    filter,
                    urlSlug,
                    pageSize: DEFAULT_PAGE_SIZE * (loadMore + 1),
                })
                .toPromise();

            const [categoryDetailResponse] = await Promise.all([
                categoryDetailResponsePromise,
                categoryProductsResponsePromise,
            ]);

            categoryUuid = categoryDetailResponse.data?.category?.uuid || null;

            if (getIsRedirectedFromSsr(context.req.headers)) {
                const serverSideErrorResponse = handleServerSideErrorResponseForFriendlyUrls(
                    categoryDetailResponse.error?.graphQLErrors,
                    categoryDetailResponse.data?.category,
                    context.res,
                );

                if (serverSideErrorResponse) {
                    return serverSideErrorResponse;
                }
            }

            const initServerSideData = await initServerSideProps<TypeAdvertsQueryVariables>({
                domainConfig,
                context,
                client,
                ssrExchange,
                prefetchedQueries: [
                    {
                        query: AdvertsQueryDocument,
                        variables: {
                            positionNames: ['productListSecondRow'],
                            categoryUuid,
                        },
                    },
                ],
            });

            return initServerSideData;
        },
);

export default CategoryDetailPage;
