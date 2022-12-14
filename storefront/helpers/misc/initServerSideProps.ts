import { logException } from '../errors/logException';
import { createClient } from '../urql/createClient';
import { DEFAULT_PAGE_SIZE } from 'components/Blocks/Pagination/Pagination';
import { DocumentNode } from 'graphql';
import {
    AdvertsQueryDocumentApi,
    ArticlePlacementTypeEnumApi,
    ArticlesQueryDocumentApi,
    BlogCategoryArticlesDocumentApi,
    BrandProductsQueryDocumentApi,
    CategoryProductsQueryDocumentApi,
    CurrentCustomerUserQueryApi,
    CurrentCustomerUserQueryDocumentApi,
    FlagProductsQueryDocumentApi,
    NavigationQueryDocumentApi,
    NotificationBarsDocumentApi,
    SettingsQueryDocumentApi,
} from 'graphql/generated';
import { getFilterOptions } from 'helpers/filterOptions/getFilterOptions';
import { mapParametersFilter } from 'helpers/filterOptions/mapParametersFilter';
import { parseFilterOptionsFromQuery } from 'helpers/filterOptions/parseFilterOptionsFromQuery';
import {
    getInternationalizedStaticUrls,
    getServerSideInternationalizedStaticUrl,
} from 'helpers/localization/getInternationalizedStaticUrls';
import { getNewPagination } from 'helpers/pagination/getNewPagination';
import { parsePageNumberFromQuery } from 'helpers/pagination/parsePageNumberFromQuery';
import { getStringFromUrlQuery } from 'helpers/parsing/getStringFromUrlQuery';
import {
    FILTER_QUERY_PARAMETER_NAME,
    PAGE_QUERY_PARAMETER_NAME,
    SORT_QUERY_PARAMETER_NAME,
} from 'helpers/queryParams/queryParamNames';
import { getProductListSort } from 'helpers/sorting/getProductListSort';
import { GetServerSidePropsContext, GetServerSidePropsResult } from 'next';
import loadNamespaces from 'next-translate/loadNamespaces';
import { SSRData, SSRExchange } from 'next-urql';
import { AppStore } from 'redux/main';
import { Client, ssrExchange } from 'urql';

export type ServerSidePropsType = {
    urqlState: SSRData;
    isMaintenance: boolean;
};

export async function initServerSideProps(
    context: GetServerSidePropsContext,
    store: AppStore,
    authenticationRequired = false,
    prefetchedQueries: { query: string | DocumentNode; variables?: { [key: string]: unknown } }[] = [],
    client: Client | null = null,
    ssrCache: SSRExchange | null = null,
): Promise<GetServerSidePropsResult<ServerSidePropsType>> {
    try {
        const domainConfig = store.getState().domain;
        let currentClient = client;
        let currentSsrCache = ssrCache;

        if (currentSsrCache === null) {
            currentSsrCache = ssrExchange({ isClient: false });
        }

        if (currentClient === null) {
            // eslint-disable-next-line require-atomic-updates
            currentClient = await createClient(context, store, currentSsrCache);
        }

        if (currentClient !== null) {
            prefetchedQueries.push({ query: NotificationBarsDocumentApi });
            prefetchedQueries.push({ query: NavigationQueryDocumentApi });
            prefetchedQueries.push({
                query: ArticlesQueryDocumentApi,
                variables: {
                    placement: [
                        ArticlePlacementTypeEnumApi.Footer1Api,
                        ArticlePlacementTypeEnumApi.Footer2Api,
                        ArticlePlacementTypeEnumApi.Footer3Api,
                        ArticlePlacementTypeEnumApi.Footer4Api,
                    ],
                    first: 100,
                },
            });
            prefetchedQueries.push({ query: AdvertsQueryDocumentApi });
            prefetchedQueries.push({ query: CurrentCustomerUserQueryDocumentApi });
            prefetchedQueries.push({ query: SettingsQueryDocumentApi });

            const resolvedQueries = await Promise.all(
                prefetchedQueries.map((queryObject) =>
                    currentClient!.query(queryObject.query, queryObject.variables).toPromise(),
                ),
            );
            const slugResult = resolvedQueries.find((query) => query.data?.slug?.slug !== undefined);
            const parsedSlug = slugResult?.data.slug.slug;
            const trimmedUrl = getServerSideInternationalizedStaticUrl(context, domainConfig.url);

            const entityWithProductsResult = resolvedQueries.find(
                (query) =>
                    query.data?.slug?.__typename === 'Category' ||
                    query.data?.slug?.__typename === 'Brand' ||
                    query.data?.slug?.__typename === 'Flag' ||
                    query.data?.slug?.__typename === 'BlogCategory',
            );

            if (entityWithProductsResult) {
                let document;

                if (entityWithProductsResult.data.slug.__typename === 'Category') {
                    document = CategoryProductsQueryDocumentApi;
                } else if (entityWithProductsResult.data.slug.__typename === 'Brand') {
                    document = BrandProductsQueryDocumentApi;
                } else if (entityWithProductsResult.data.slug.__typename === 'Flag') {
                    document = FlagProductsQueryDocumentApi;
                } else if (entityWithProductsResult.data.slug.__typename === 'BlogCategory') {
                    document = BlogCategoryArticlesDocumentApi;
                }

                const page = parsePageNumberFromQuery(context.query[PAGE_QUERY_PARAMETER_NAME]);
                const orderingMode = getProductListSort(
                    getStringFromUrlQuery(context.query[SORT_QUERY_PARAMETER_NAME]),
                );
                const filter = getFilterOptions(
                    parseFilterOptionsFromQuery(context.query[FILTER_QUERY_PARAMETER_NAME]),
                );

                if (document !== undefined) {
                    await currentClient
                        .query(document, {
                            endCursor: getNewPagination(page === 0 ? 1 : page).endCursor,
                            orderingMode,
                            filter: mapParametersFilter(filter),
                            uuid: entityWithProductsResult.data.slug.uuid,
                            pageSize: DEFAULT_PAGE_SIZE,
                        })
                        .toPromise();
                }
            }

            if (parsedSlug !== undefined && parsedSlug !== trimmedUrl) {
                return {
                    redirect: {
                        statusCode: 301,
                        destination: parsedSlug,
                    },
                };
            }

            if (authenticationRequired) {
                const customerQueryResult = currentClient.readQuery<CurrentCustomerUserQueryApi>(
                    CurrentCustomerUserQueryDocumentApi,
                    {},
                );

                const isLogged =
                    customerQueryResult?.data?.currentCustomerUser !== undefined &&
                    // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition
                    customerQueryResult?.data?.currentCustomerUser !== null;

                if (!isLogged) {
                    const [loginUrl, redirectTargetUrlWithLeadingSlash] = getInternationalizedStaticUrls(
                        ['/login', context.resolvedUrl],
                        domainConfig.url,
                    );
                    const redirectTargetUrl = redirectTargetUrlWithLeadingSlash.slice(1);
                    const redirectQuery = redirectTargetUrl.length > 0 ? `?r=${redirectTargetUrl}` : '';
                    const logginUrlWithRedirect = `${loginUrl}${redirectQuery}`;

                    return {
                        redirect: {
                            statusCode: 302,
                            destination: logginUrlWithRedirect,
                        },
                    };
                }
            }
            const isMaintenance = resolvedQueries.some((query) => query.error?.response.status === 503);
            if (isMaintenance) {
                // eslint-disable-next-line require-atomic-updates
                context.res.statusCode = 503;
            }

            return {
                props: {
                    ...(await loadNamespaces({ locale: domainConfig.defaultLocale, pathname: trimmedUrl })),
                    urqlState: currentSsrCache.extractData(),
                    isMaintenance,
                },
            };
        }
        return { props: {} as ServerSidePropsType };
    } catch (e) {
        logException(e);
        throw e;
    }
}
