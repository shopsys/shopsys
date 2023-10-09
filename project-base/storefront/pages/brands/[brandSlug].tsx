import { getEndCursor } from 'components/Blocks/Product/Filter/helpers/getEndCursor';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { BrandDetailContent } from 'components/Pages/BrandDetail/BrandDetailContent';
import { DEFAULT_PAGE_SIZE } from 'config/constants';
import {
    BrandDetailQueryApi,
    BrandDetailQueryDocumentApi,
    BrandDetailQueryVariablesApi,
    BrandProductsQueryApi,
    BrandProductsQueryDocumentApi,
    BrandProductsQueryVariablesApi,
    useBrandDetailQueryApi,
} from 'graphql/generated';
import { useGtmFriendlyPageViewEvent } from 'gtm/helpers/eventFactories';
import { useGtmPageViewEvent } from 'gtm/hooks/useGtmPageViewEvent';
import { getMappedProductFilter } from 'helpers/filterOptions/getMappedProductFilter';
import { mapParametersFilter } from 'helpers/filterOptions/mapParametersFilter';
import { isRedirectedFromSsr } from 'helpers/isRedirectedFromSsr';
import { getRedirectWithOffsetPage } from 'helpers/loadMore';
import {
    getNumberFromUrlQuery,
    getProductListSortFromUrlQuery,
    getSlugFromServerSideUrl,
    getSlugFromUrl,
} from 'helpers/parsing/urlParsing';
import {
    FILTER_QUERY_PARAMETER_NAME,
    LOAD_MORE_QUERY_PARAMETER_NAME,
    PAGE_QUERY_PARAMETER_NAME,
    SORT_QUERY_PARAMETER_NAME,
} from 'helpers/queryParamNames';
import { getServerSidePropsWrapper } from 'helpers/serverSide/getServerSidePropsWrapper';
import { initServerSideProps } from 'helpers/serverSide/initServerSideProps';
import { useSeoTitleWithPagination } from 'hooks/seo/useSeoTitleWithPagination';
import { useQueryParams } from 'hooks/useQueryParams';
import { NextPage } from 'next';
import { useRouter } from 'next/router';
import { createClient } from 'urql/createClient';

const BrandDetailPage: NextPage = () => {
    const router = useRouter();
    const { sort, filter } = useQueryParams();
    const [{ data: brandDetailData, fetching }] = useBrandDetailQueryApi({
        variables: {
            urlSlug: getSlugFromUrl(router.asPath),
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
        <CommonLayout
            breadcrumbs={brandDetailData?.brand?.breadcrumb}
            breadcrumbsType="category"
            description={brandDetailData?.brand?.seoMetaDescription}
            isFetchingData={!filter && fetching && !brandDetailData}
            title={seoTitle}
        >
            {!!brandDetailData?.brand && <BrandDetailContent brand={brandDetailData.brand} />}
        </CommonLayout>
    );
};

export const getServerSideProps = getServerSidePropsWrapper(
    ({ redisClient, domainConfig, ssrExchange, t }) =>
        async (context) => {
            const urlSlug = getSlugFromServerSideUrl(context.req.url ?? '');
            const page = getNumberFromUrlQuery(context.query[PAGE_QUERY_PARAMETER_NAME], 0);
            const loadMore = getNumberFromUrlQuery(context.query[LOAD_MORE_QUERY_PARAMETER_NAME], 1);
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
                const orderingMode = getProductListSortFromUrlQuery(context.query[SORT_QUERY_PARAMETER_NAME]);
                const filter = getMappedProductFilter(context.query[FILTER_QUERY_PARAMETER_NAME]);

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
