import { getEndCursor } from 'components/Blocks/Product/Filter/helpers/getEndCursor';
import { Breadcrumbs } from 'components/Layout/Breadcrumbs/Breadcrumbs';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { Webline } from 'components/Layout/Webline/Webline';
import { CategoryDetailPageSkeleton } from 'components/Pages/CategoryDetail/CategoryDetailPageSkeleton';
import { FlagDetailContent } from 'components/Pages/FlagDetail/FlagDetailContent';
import {
    FlagDetailQueryApi,
    FlagDetailQueryDocumentApi,
    FlagDetailQueryVariablesApi,
    FlagProductsQueryApi,
    FlagProductsQueryDocumentApi,
    FlagProductsQueryVariablesApi,
    useFlagDetailQueryApi,
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
import { createClient } from 'urql/createClient';

import { useGtmPageViewEvent } from 'hooks/gtm/useGtmPageViewEvent';
import { NextPage } from 'next';
import { useRouter } from 'next/router';
import { getSlugFromServerSideUrl, getSlugFromUrl } from 'helpers/parsing/getSlugFromUrl';
import { getUrlWithoutGetParameters } from 'helpers/parsing/getUrlWithoutGetParameters';
import { useSeoTitleWithPagination } from 'hooks/seo/useSeoTitleWithPagination';
import { DEFAULT_PAGE_SIZE } from 'config/constants';
import { parseLoadMoreFromQuery } from 'helpers/pagination/parseLoadMoreFromQuery';
import { getRedirectWithOffsetPage } from 'helpers/pagination/loadMore';

const FlagDetailPage: NextPage = () => {
    const router = useRouter();
    const slug = getUrlWithoutGetParameters(router.asPath);

    const orderingMode = getProductListSort(parseProductListSortFromQuery(router.query[SORT_QUERY_PARAMETER_NAME]));
    const filter = mapParametersFilter(
        getFilterOptions(parseFilterOptionsFromQuery(router.query[FILTER_QUERY_PARAMETER_NAME])),
    );

    const [{ data: flagDetailData, fetching }] = useFlagDetailQueryApi({
        variables: {
            urlSlug: getSlugFromUrl(slug),
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
