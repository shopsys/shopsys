import { DEFAULT_PAGE_SIZE } from 'config/constants';
import { getEndCursor } from 'components/Blocks/Product/Filter/helpers/getEndCursor';
import { Breadcrumbs } from 'components/Layout/Breadcrumbs/Breadcrumbs';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { CategoryDetailContent } from 'components/Pages/CategoryDetail/CategoryDetailContent';
import { CategoryDetailPageSkeleton } from 'components/Pages/CategoryDetail/CategoryDetailPageSkeleton';
import { useCategoryDetailData } from 'components/Pages/CategoryDetail/helpers';
import {
    CategoryDetailQueryApi,
    CategoryDetailQueryVariablesApi,
    CategoryDetailQueryDocumentApi,
    CategoryProductsQueryDocumentApi,
} from 'graphql/generated';

import { getFilterOptions } from 'helpers/filterOptions/getFilterOptions';
import { mapParametersFilter } from 'helpers/filterOptions/mapParametersFilter';
import { parseFilterOptionsFromQuery } from 'helpers/filterOptions/parseFilterOptionsFromQuery';
import { useHandleDefaultFiltersUpdate } from 'helpers/filterOptions/seoCategories';
import { useGtmFriendlyPageViewEvent } from 'helpers/gtm/eventFactories';
import { getServerSidePropsWrapper } from 'helpers/misc/getServerSidePropsWrapper';
import { initServerSideProps } from 'helpers/misc/initServerSideProps';
import { isRedirectedFromSsr } from 'helpers/misc/isServer';
import { parseLoadMoreFromQuery } from 'helpers/pagination/parseLoadMoreFromQuery';
import { parsePageNumberFromQuery } from 'helpers/pagination/parsePageNumberFromQuery';
import {
    PAGE_QUERY_PARAMETER_NAME,
    SORT_QUERY_PARAMETER_NAME,
    FILTER_QUERY_PARAMETER_NAME,
    LOAD_MORE_QUERY_PARAMETER_NAME,
} from 'helpers/queryParams/queryParamNames';
import { getProductListSort } from 'helpers/sorting/getProductListSort';
import { parseProductListSortFromQuery } from 'helpers/sorting/parseProductListSortFromQuery';
import { createClient } from 'helpers/urql/createClient';
import { useGtmPageViewEvent } from 'hooks/gtm/useGtmPageViewEvent';
import { useSeoTitleWithPagination } from 'hooks/seo/useSeoTitleWithPagination';
import { useQueryParams } from 'hooks/useQueryParams';
import { NextPage } from 'next';
import { useSessionStore } from 'store/zustand/useSessionStore';
import { getSlugFromServerSideUrl } from 'utils/getSlugFromUrl';
import { getRedirectWithOffsetPage } from 'helpers/pagination/loadMore';

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
            const page = parsePageNumberFromQuery(context.query[PAGE_QUERY_PARAMETER_NAME]);
            const loadMore = parseLoadMoreFromQuery(context.query[LOAD_MORE_QUERY_PARAMETER_NAME]);
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

            const orderingMode = getProductListSort(
                parseProductListSortFromQuery(context.query[SORT_QUERY_PARAMETER_NAME]),
            );
            const optionsFilter = getFilterOptions(
                parseFilterOptionsFromQuery(context.query[FILTER_QUERY_PARAMETER_NAME]),
            );

            if (isRedirectedFromSsr(context.req.headers)) {
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
