import { getEndCursor } from 'components/Blocks/Product/Filter/helpers/getEndCursor';
import { Breadcrumbs } from 'components/Layout/Breadcrumbs/Breadcrumbs';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { CategoryDetailPageSkeleton } from 'components/Pages/CategoryDetail/CategoryDetailPageSkeleton';
import { FlagDetailContent } from 'components/Pages/FlagDetail/FlagDetailContent';
import { getMappedProductFilter } from 'helpers/filterOptions/getMappedProductFilter';
import { useGtmFriendlyPageViewEvent } from 'gtm/helpers/eventFactories';
import { getServerSidePropsWrapper } from 'helpers/serverSide/getServerSidePropsWrapper';
import { initServerSideProps } from 'helpers/serverSide/initServerSideProps';
import { isRedirectedFromSsr } from 'helpers/isServer';
import {
    FILTER_QUERY_PARAMETER_NAME,
    LOAD_MORE_QUERY_PARAMETER_NAME,
    PAGE_QUERY_PARAMETER_NAME,
    SORT_QUERY_PARAMETER_NAME,
} from 'helpers/queryParamNames';
import { createClient } from 'urql/createClient';
import { useGtmPageViewEvent } from 'gtm/hooks/useGtmPageViewEvent';
import { NextPage } from 'next';
import { useRouter } from 'next/router';
import {
    getNumberFromUrlQuery,
    getProductListSortFromUrlQuery,
    getSlugFromServerSideUrl,
    getSlugFromUrl,
} from 'helpers/parsing/urlParsing';
import { useSeoTitleWithPagination } from 'hooks/seo/useSeoTitleWithPagination';
import { DEFAULT_PAGE_SIZE } from 'config/constants';
import { getRedirectWithOffsetPage } from 'helpers/loadMore';
import {
    useFlagDetailQueryApi,
    FlagDetailQueryApi,
    FlagDetailQueryVariablesApi,
    FlagDetailQueryDocumentApi,
} from 'graphql/requests/flags/queries/FlagDetailQuery.generated';
import {
    FlagProductsQueryApi,
    FlagProductsQueryVariablesApi,
    FlagProductsQueryDocumentApi,
} from 'graphql/requests/products/queries/FlagProductsQuery.generated';

const FlagDetailPage: NextPage = () => {
    const router = useRouter();
    const orderingMode = getProductListSortFromUrlQuery(router.query[SORT_QUERY_PARAMETER_NAME]);
    const filter = getMappedProductFilter(router.query[FILTER_QUERY_PARAMETER_NAME]);

    const [{ data: flagDetailData, fetching }] = useFlagDetailQueryApi({
        variables: {
            urlSlug: getSlugFromUrl(router.asPath),
            orderingMode,
            filter,
        },
    });

    const seoTitle = useSeoTitleWithPagination(flagDetailData?.flag?.products.totalCount, flagDetailData?.flag?.name);

    const pageViewEvent = useGtmFriendlyPageViewEvent(flagDetailData?.flag);
    useGtmPageViewEvent(pageViewEvent, fetching);

    return (
        <CommonLayout title={seoTitle}>
            {!!flagDetailData?.flag?.breadcrumb && (
                <Webline>
                    <Breadcrumbs key="breadcrumb" breadcrumb={flagDetailData.flag.breadcrumb} />
                </Webline>
            )}
            {!filter && fetching ? (
                <CategoryDetailPageSkeleton />
            ) : (
                !!flagDetailData?.flag && <FlagDetailContent flag={flagDetailData.flag} />
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

                const flagDetailResponsePromise = client!
                    .query<FlagDetailQueryApi, FlagDetailQueryVariablesApi>(FlagDetailQueryDocumentApi, {
                        urlSlug,
                        filter,
                        orderingMode,
                    })
                    .toPromise();

                const flagProductsResponsePromise = client!
                    .query<FlagProductsQueryApi, FlagProductsQueryVariablesApi>(FlagProductsQueryDocumentApi, {
                        endCursor: getEndCursor(page),
                        orderingMode,
                        filter,
                        urlSlug,
                        pageSize: DEFAULT_PAGE_SIZE * (loadMore + 1),
                    })
                    .toPromise();

                const [flagDetailResponse] = await Promise.all([
                    flagDetailResponsePromise,
                    flagProductsResponsePromise,
                ]);

                if ((!flagDetailResponse.data || !flagDetailResponse.data.flag) && !(context.res.statusCode === 503)) {
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

export default FlagDetailPage;
