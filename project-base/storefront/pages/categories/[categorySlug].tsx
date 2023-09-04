import { DEFAULT_PAGE_SIZE } from 'config/constants';
import { getEndCursor } from 'components/Blocks/Product/Filter/helpers/getEndCursor';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { CategoryDetailContent } from 'components/Pages/CategoryDetail/CategoryDetailContent';
import { CategoryDetailPageSkeleton } from 'components/Pages/CategoryDetail/CategoryDetailPageSkeleton';
import { useCategoryDetailData } from 'components/Pages/CategoryDetail/helpers';
import {
    CategoryDetailQueryApi,
    CategoryDetailQueryVariablesApi,
    CategoryDetailQueryDocumentApi,
    CategoryProductsQueryDocumentApi,
} from 'graphql/generated';
import { getMappedProductFilter } from 'helpers/filterOptions/getMappedProductFilter';
import { mapParametersFilter } from 'helpers/filterOptions/mapParametersFilter';
import { useHandleDefaultFiltersUpdate } from 'helpers/filterOptions/seoCategories';
import { useGtmFriendlyPageViewEvent } from 'gtm/helpers/eventFactories';
import { getServerSidePropsWrapper } from 'helpers/serverSide/getServerSidePropsWrapper';
import { initServerSideProps } from 'helpers/serverSide/initServerSideProps';
import { isRedirectedFromSsr } from 'helpers/isServer';
import {
    PAGE_QUERY_PARAMETER_NAME,
    SORT_QUERY_PARAMETER_NAME,
    FILTER_QUERY_PARAMETER_NAME,
    LOAD_MORE_QUERY_PARAMETER_NAME,
} from 'helpers/queryParamNames';
import { createClient } from 'urql/createClient';
import { useGtmPageViewEvent } from 'gtm/hooks/useGtmPageViewEvent';
import { useSeoTitleWithPagination } from 'hooks/seo/useSeoTitleWithPagination';
import { useQueryParams } from 'hooks/useQueryParams';
import { NextPage } from 'next';
import { useSessionStore } from 'store/useSessionStore';
import { getRedirectWithOffsetPage } from 'helpers/loadMore';
import {
    getNumberFromUrlQuery,
    getProductListSortFromUrlQuery,
    getSlugFromServerSideUrl,
} from 'helpers/parsing/urlParsing';

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
        <CommonLayout
            title={seoTitle}
            description={categoryData?.seoMetaDescription}
            breadcrumbs={categoryData?.breadcrumb}
            breadcrumbsType="category"
        >
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
