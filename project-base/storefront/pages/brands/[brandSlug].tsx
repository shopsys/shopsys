import { getEndCursor } from 'components/Blocks/Product/Filter/helpers/getEndCursor';
import { Breadcrumbs } from 'components/Layout/Breadcrumbs/Breadcrumbs';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { BrandDetailContent } from 'components/Pages/BrandDetail/BrandDetailContent';
import { CategoryDetailPageSkeleton } from 'components/Pages/CategoryDetail/CategoryDetailPageSkeleton';
import {
    BrandDetailQueryApi,
    BrandDetailQueryDocumentApi,
    BrandDetailQueryVariablesApi,
    BrandProductsQueryApi,
    BrandProductsQueryDocumentApi,
    BrandProductsQueryVariablesApi,
    useBrandDetailQueryApi,
} from 'graphql/generated';

import { getFilterOptions } from 'helpers/filterOptions/getFilterOptions';
import { mapParametersFilter } from 'helpers/filterOptions/mapParametersFilter';
import { parseFilterOptionsFromQuery } from 'helpers/filterOptions/parseFilterOptionsFromQuery';
import { useGtmFriendlyPageViewEvent } from 'helpers/gtm/eventFactories';
import { getServerSidePropsWrapper } from 'helpers/misc/getServerSidePropsWrapper';
import { initServerSideProps } from 'helpers/misc/initServerSideProps';
import { isRedirectedFromSsr } from 'helpers/misc/isServer';
import { parsePageNumberFromQuery } from 'helpers/pagination/parsePageNumberFromQuery';
import {
    FILTER_QUERY_PARAMETER_NAME,
    LOAD_MORE_QUERY_PARAMETER_NAME,
    PAGE_QUERY_PARAMETER_NAME,
    SORT_QUERY_PARAMETER_NAME,
} from 'helpers/queryParams/queryParamNames';
import { getProductListSort } from 'helpers/sorting/getProductListSort';
import { parseProductListSortFromQuery } from 'helpers/sorting/parseProductListSortFromQuery';
import { createClient } from 'helpers/urql/createClient';

import { useGtmPageViewEvent } from 'hooks/gtm/useGtmPageViewEvent';
import { NextPage } from 'next';
import { useRouter } from 'next/router';
import { getSlugFromServerSideUrl, getSlugFromUrl } from 'utils/getSlugFromUrl';
import { getUrlWithoutGetParameters } from 'helpers/parsing/getUrlWithoutGetParameters';
import { useSeoTitleWithPagination } from 'hooks/seo/useSeoTitleWithPagination';
import { useQueryParams } from 'hooks/useQueryParams';
import { DEFAULT_PAGE_SIZE } from 'config/constants';
import { parseLoadMoreFromQuery } from 'helpers/pagination/parseLoadMoreFromQuery';
import { getRedirectWithOffsetPage } from 'helpers/pagination/loadMore';

const BrandDetailPage: NextPage = () => {
    const router = useRouter();
    const slug = getUrlWithoutGetParameters(router.asPath);

    const { sort, filter } = useQueryParams();

    const [{ data: brandDetailData, fetching }] = useBrandDetailQueryApi({
        variables: {
            urlSlug: getSlugFromUrl(slug),
            orderingMode: sort,
            filter: mapParametersFilter(filter),
        },
    });

    const seoTitle = useSeoTitleWithPagination(
        brandDetailData?.brand?.products.totalCount,
        brandDetailData?.brand?.name,
        brandDetailData?.brand?.seoTitle,
    );

    const pageViewEvent = useGtmFriendlyPageViewEvent(brandDetailData?.brand);
    useGtmPageViewEvent(pageViewEvent, fetching);

    return (
        <CommonLayout title={seoTitle} description={brandDetailData?.brand?.seoMetaDescription}>
            {!!brandDetailData?.brand?.breadcrumb && (
                <Webline>
                    <Breadcrumbs key="breadcrumb" breadcrumb={brandDetailData.brand.breadcrumb} />
                </Webline>
            )}
            {!filter && fetching ? (
                <CategoryDetailPageSkeleton />
            ) : (
                !!brandDetailData?.brand && <BrandDetailContent brand={brandDetailData.brand} />
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

            const client = createClient({
                t,
                ssrExchange,
                publicGraphqlEndpoint: domainConfig.publicGraphqlEndpoint,
                redisClient,
                context,
            });

            if (isRedirectedFromSsr(context.req.headers)) {
                const orderingMode = getProductListSort(
                    parseProductListSortFromQuery(context.query[SORT_QUERY_PARAMETER_NAME]),
                );
                const filter = mapParametersFilter(
                    getFilterOptions(parseFilterOptionsFromQuery(context.query[FILTER_QUERY_PARAMETER_NAME])),
                );

                const brandDetailResponsePromise = client!
                    .query<BrandDetailQueryApi, BrandDetailQueryVariablesApi>(BrandDetailQueryDocumentApi, {
                        urlSlug,
                        orderingMode,
                        filter,
                    })
                    .toPromise();

                const brandProductsResponsePromise = client!
                    .query<BrandProductsQueryApi, BrandProductsQueryVariablesApi>(BrandProductsQueryDocumentApi, {
                        endCursor: getEndCursor(page),
                        orderingMode,
                        filter,
                        urlSlug,
                        pageSize: DEFAULT_PAGE_SIZE * (loadMore + 1),
                    })
                    .toPromise();

                const [brandDetailResponse] = await Promise.all([
                    brandDetailResponsePromise,
                    brandProductsResponsePromise,
                ]);

                if (
                    (!brandDetailResponse.data || !brandDetailResponse.data.brand) &&
                    !(context.res.statusCode === 503)
                ) {
                    return {
                        notFound: true,
                    };
                }
            }

            const initServerSideData = await initServerSideProps({
                context,
                client,
                ssrExchange,
                domainConfig,
            });

            return initServerSideData;
        },
);

export default BrandDetailPage;
