/* eslint-disable no-param-reassign */
import { logException } from '../errors/logException';
import { createClient } from '../urql/createClient';
import { getUnauthenticatedRedirectSSR } from './getUnauthenticatedRedirectSSR';
import { isUserLoggedInSSR } from './isUserLoggedInSSR';
import { DEFAULT_PAGE_SIZE } from 'components/Blocks/Pagination/Pagination';
import { getEndCursor } from 'components/Blocks/Product/Filter/helpers/getEndCursor';
import { DocumentNode } from 'graphql';
import {
    AdvertsQueryDocumentApi,
    ArticlePlacementTypeEnumApi,
    ArticlesQueryDocumentApi,
    BlogCategoryArticlesDocumentApi,
    BrandProductsQueryDocumentApi,
    CategoryProductsQueryDocumentApi,
    CurrentCustomerUserQueryDocumentApi,
    FlagProductsQueryDocumentApi,
    NavigationQueryDocumentApi,
    NotificationBarsDocumentApi,
    ProductsByCatnumsDocumentApi,
    SeoPageQueryDocumentApi,
    SettingsQueryDocumentApi,
} from 'graphql/generated';
import { DomainConfigType, getDomainConfig } from 'helpers/domain/domain';
import { getFilterOptions } from 'helpers/filterOptions/getFilterOptions';
import { mapParametersFilter } from 'helpers/filterOptions/mapParametersFilter';
import { parseFilterOptionsFromQuery } from 'helpers/filterOptions/parseFilterOptionsFromQuery';
import { getServerSideInternationalizedStaticUrl } from 'helpers/localization/getInternationalizedStaticUrls';
import { parsePageNumberFromQuery } from 'helpers/pagination/parsePageNumberFromQuery';
import { getStringFromUrlQuery } from 'helpers/parsing/getStringFromUrlQuery';
import { getUrlWithoutGetParameters } from 'helpers/parsing/getUrlWithoutGetParameters';
import {
    FILTER_QUERY_PARAMETER_NAME,
    PAGE_QUERY_PARAMETER_NAME,
    SORT_QUERY_PARAMETER_NAME,
} from 'helpers/queryParams/queryParamNames';
import { extractSeoPageSlugFromUrl } from 'helpers/seo/extractSeoPageSlugFromUrl';
import { getProductListSort } from 'helpers/sorting/getProductListSort';
import { GetServerSidePropsContext, GetServerSidePropsResult } from 'next';
import loadNamespaces from 'next-translate/loadNamespaces';
import { SSRData, SSRExchange } from 'next-urql';
import { RedisClientType, RedisModules, RedisScripts } from 'redis';
import { Client, ssrExchange } from 'urql';
import { parseCatnums } from 'utils/grapesJsParser';

export type ServerSidePropsType = {
    urqlState: SSRData;
    isMaintenance: boolean;
    domainConfig: DomainConfigType;
};

type InitServerSidePropsParameters = {
    context: GetServerSidePropsContext;
    authenticationRequired?: boolean;
    prefetchedQueries?: { query: string | DocumentNode; variables?: { [key: string]: unknown } }[];
    redisClient: RedisClientType<any & RedisModules, RedisScripts>;
    client?: Client | null;
    ssrCache?: SSRExchange;
};

export const initServerSideProps = async ({
    context,
    authenticationRequired = false,
    prefetchedQueries = [],
    redisClient,
    client,
    ssrCache,
}: InitServerSidePropsParameters): Promise<GetServerSidePropsResult<ServerSidePropsType>> => {
    try {
        const domainConfig = getDomainConfig(context.req.headers.host!);
        const currentSsrCache = ssrCache ?? ssrExchange({ isClient: false });
        const currentClient =
            client ?? (await createClient(context, domainConfig.publicGraphqlEndpoint, currentSsrCache, redisClient));

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

            const seoPageSlug = extractSeoPageSlugFromUrl(context.resolvedUrl, domainConfig.url);

            if (seoPageSlug !== null) {
                prefetchedQueries.push({
                    query: SeoPageQueryDocumentApi,
                    variables: {
                        pageSlug: seoPageSlug,
                    },
                });
            }

            const resolvedQueries = await Promise.all(
                prefetchedQueries.map((queryObject) =>
                    currentClient.query(queryObject.query, queryObject.variables).toPromise(),
                ),
            );
            const slugResult = resolvedQueries.find((query) => query.data?.slug?.slug !== undefined);
            const parsedSlug = slugResult?.data.slug.slug;
            const { trimmedUrlWithoutQueryParams, queryParams } = getServerSideInternationalizedStaticUrl(
                context,
                domainConfig.url,
            );

            const entityWithProductsResult = resolvedQueries.find((query) =>
                ['Category', 'Brand', 'Flag', 'BlogCategory'].includes(query.data?.slug?.__typename),
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
                            endCursor: getEndCursor(page),
                            orderingMode,
                            filter: mapParametersFilter(filter),
                            uuid: entityWithProductsResult.data.slug.uuid,
                            pageSize: DEFAULT_PAGE_SIZE,
                        })
                        .toPromise();
                }
            }

            const articleWithGrapesJsResult = resolvedQueries.find((query) =>
                ['BlogArticle', 'Article'].includes(query.data?.slug?.__typename),
            );
            if (articleWithGrapesJsResult) {
                const parsedCatnums = parseCatnums(articleWithGrapesJsResult.data.slug.text);
                await currentClient.query(ProductsByCatnumsDocumentApi, { catnums: parsedCatnums }).toPromise();
            }

            if (parsedSlug !== undefined && parsedSlug !== trimmedUrlWithoutQueryParams) {
                return {
                    redirect: {
                        statusCode: 301,
                        destination: `${parsedSlug}${queryParams ?? ''}`,
                    },
                };
            }

            if (authenticationRequired) {
                const isUserLoggedIn = isUserLoggedInSSR(currentClient);

                if (!isUserLoggedIn) {
                    return getUnauthenticatedRedirectSSR(
                        getUrlWithoutGetParameters(context.resolvedUrl),
                        domainConfig.url,
                    );
                }
            }

            const isMaintenance = resolvedQueries.some((query) => query.error?.response.status === 503);
            if (isMaintenance) {
                // eslint-disable-next-line require-atomic-updates
                context.res.statusCode = 503;
            }

            return {
                props: {
                    ...(await loadNamespaces({
                        locale: domainConfig.defaultLocale,
                        pathname: trimmedUrlWithoutQueryParams,
                    })),
                    domainConfig,
                    // JSON.parse(JSON.stringify()) fix of https://github.com/vercel/next.js/issues/11993
                    urqlState: JSON.parse(JSON.stringify(currentSsrCache.extractData())),
                    isMaintenance,
                },
            };
        }
        return { props: {} as ServerSidePropsType };
    } catch (e) {
        logException(e);
        throw e;
    }
};
