import { getEndCursor } from 'components/Blocks/Product/Filter/helpers/getEndCursor';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { FlagDetailContent } from 'components/Pages/FlagDetail/FlagDetailContent';
import { DEFAULT_PAGE_SIZE } from 'config/constants';
import {
    useFlagDetailQuery,
    FlagDetailQuery,
    FlagDetailQueryVariables,
    FlagDetailQueryDocument,
} from 'graphql/requests/flags/queries/FlagDetailQuery.generated';
import {
    FlagProductsQuery,
    FlagProductsQueryVariables,
    FlagProductsQueryDocument,
} from 'graphql/requests/products/queries/FlagProductsQuery.generated';
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
import { NextPage } from 'next';
import { useRouter } from 'next/router';
import { createClient } from 'urql/createClient';

const FlagDetailPage: NextPage = () => {
    const router = useRouter();
    const orderingMode = getProductListSortFromUrlQuery(router.query[SORT_QUERY_PARAMETER_NAME]);
    const filter = getMappedProductFilter(router.query[FILTER_QUERY_PARAMETER_NAME]);

    const [{ data: flagDetailData, fetching }] = useFlagDetailQuery({
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
        <CommonLayout
            breadcrumbs={flagDetailData?.flag?.breadcrumb}
            breadcrumbsType="category"
            hreflangLinks={flagDetailData?.flag?.hreflangLinks}
            isFetchingData={!filter && fetching && !flagDetailData}
            title={seoTitle}
        >
            {!!flagDetailData?.flag && <FlagDetailContent flag={flagDetailData.flag} />}
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
                    .query<FlagDetailQuery, FlagDetailQueryVariables>(FlagDetailQueryDocument, {
                        urlSlug,
                        filter,
                        orderingMode,
                    })
                    .toPromise();

                const flagProductsResponsePromise = client!
                    .query<FlagProductsQuery, FlagProductsQueryVariables>(FlagProductsQueryDocument, {
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

                const serverSideErrorResponse = handleServerSideErrorResponseForFriendlyUrls(
                    flagDetailResponse.error?.graphQLErrors,
                    flagDetailResponse.data?.flag,
                    context.res,
                );

                if (serverSideErrorResponse) {
                    return serverSideErrorResponse;
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
