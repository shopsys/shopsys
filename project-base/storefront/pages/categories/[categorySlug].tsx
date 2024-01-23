import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { getEndCursor } from 'components/Blocks/Product/Filter/helpers/getEndCursor';
import { LastVisitedProducts } from 'components/Blocks/Product/LastVisitedProducts/LastVisitedProducts';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { CategoryDetailContent } from 'components/Pages/CategoryDetail/CategoryDetailContent';
import { useCategoryDetailData } from 'components/Pages/CategoryDetail/helpers';
import { DEFAULT_PAGE_SIZE } from 'config/constants';
import {
    CategoryDetailQueryApi,
    CategoryDetailQueryVariablesApi,
    CategoryDetailQueryDocumentApi,
    CategoryProductsQueryDocumentApi,
} from 'graphql/generated';
import { useGtmFriendlyPageViewEvent } from 'gtm/helpers/eventFactories';
import { useGtmPageViewEvent } from 'gtm/hooks/useGtmPageViewEvent';
import { handleServerSideErrorResponseForFriendlyUrls } from 'helpers/errors/handleServerSideErrorResponseForFriendlyUrls';
import { getMappedProductFilter } from 'helpers/filterOptions/getMappedProductFilter';
import { isRedirectedFromSsr } from 'helpers/isRedirectedFromSsr';
import { getRedirectWithOffsetPage } from 'helpers/loadMore';
import {
    getNumberFromUrlQuery,
    getProductListSortFromUrlQuery,
    getSlugFromServerSideUrl,
} from 'helpers/parsing/urlParsing';
import {
    PAGE_QUERY_PARAMETER_NAME,
    SORT_QUERY_PARAMETER_NAME,
    FILTER_QUERY_PARAMETER_NAME,
    LOAD_MORE_QUERY_PARAMETER_NAME,
} from 'helpers/queryParamNames';
import { getServerSidePropsWrapper } from 'helpers/serverSide/getServerSidePropsWrapper';
import { ServerSidePropsType, initServerSideProps } from 'helpers/serverSide/initServerSideProps';
import { useSeoTitleWithPagination } from 'hooks/seo/useSeoTitleWithPagination';
import { useHandleDefaultFiltersUpdate } from 'hooks/seoCategories/useHandleDefaultFiltersUpdate';
import { useQueryParams } from 'hooks/useQueryParams';
import { NextPage } from 'next';
import { createClient } from 'urql/createClient';

const CategoryDetailPage: NextPage<ServerSidePropsType> = ({ cookies }) => {
    const { filter } = useQueryParams();
    const { categoryData, isFetchingVisible } = useCategoryDetailData(filter);

    useHandleDefaultFiltersUpdate(categoryData?.products);

    const seoTitle = useSeoTitleWithPagination(
        categoryData?.products.totalCount,
        categoryData?.name,
        categoryData?.seoTitle,
    );

    const pageViewEvent = useGtmFriendlyPageViewEvent(categoryData);
    useGtmPageViewEvent(pageViewEvent, isFetchingVisible);

    return (
        <>
            {!!filter && <MetaRobots content="noindex, follow" />}

            <CommonLayout
                breadcrumbs={categoryData?.breadcrumb}
                breadcrumbsType="category"
                description={categoryData?.seoMetaDescription}
                isFetchingData={isFetchingVisible}
                title={seoTitle}
            >
                {!!categoryData && <CategoryDetailContent category={categoryData} />}
                <LastVisitedProducts lastVisitedProductsFromCookies={cookies.lastVisitedProducts} />
            </CommonLayout>
        </>
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

            if (isRedirectedFromSsr(context.req.headers)) {
                const filter = getMappedProductFilter(context.query[FILTER_QUERY_PARAMETER_NAME]);
                const orderingMode = getProductListSortFromUrlQuery(context.query[SORT_QUERY_PARAMETER_NAME]);
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
                        pageSize: DEFAULT_PAGE_SIZE * (loadMore + 1),
                    })
                    .toPromise();

                const [categoryDetailResponse] = await Promise.all([
                    categoryDetailResponsePromise,
                    categoryProductsResponsePromise,
                ]);

                const serverSideErrorResponse = handleServerSideErrorResponseForFriendlyUrls(
                    categoryDetailResponse.error?.graphQLErrors,
                    categoryDetailResponse.data?.category,
                    context.res,
                );

                if (serverSideErrorResponse) {
                    return serverSideErrorResponse;
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
