import { logException } from '../errors/logException';
import { createClient } from '../urql/createClient';
import { getUnauthenticatedRedirectSSR } from './getUnauthenticatedRedirectSSR';
import { isUserLoggedInSSR } from './isUserLoggedInSSR';
import { DocumentNode } from 'graphql';
import {
    AdvertsQueryDocumentApi,
    ArticlePlacementTypeEnumApi,
    ArticlesQueryDocumentApi,
    CurrentCustomerUserQueryDocumentApi,
    NavigationQueryDocumentApi,
    NotificationBarsDocumentApi,
    ProductsByCatnumsDocumentApi,
    SeoPageQueryDocumentApi,
    SettingsQueryDocumentApi,
} from 'graphql/generated';
import { DomainConfigType } from 'helpers/domain/domain';
import { getServerSideInternationalizedStaticUrl } from 'helpers/localization/getInternationalizedStaticUrls';
import { getUrlWithoutGetParameters } from 'helpers/parsing/getUrlWithoutGetParameters';
import { extractSeoPageSlugFromUrl } from 'helpers/seo/extractSeoPageSlugFromUrl';
import { GetServerSidePropsContext, GetServerSidePropsResult } from 'next';
import loadNamespaces from 'next-translate/loadNamespaces';
import { RedisClientType, RedisModules, RedisScripts } from 'redis';
import { Client, SSRData, SSRExchange, ssrExchange } from 'urql';
import { parseCatnums } from 'utils/grapesJsParser';
import { Translate } from 'next-translate';

export type ServerSidePropsType = {
    urqlState: SSRData;
    isMaintenance: boolean;
    domainConfig: DomainConfigType;
};

type InitServerSidePropsParameters = {
    domainConfig: DomainConfigType;
    context: GetServerSidePropsContext;
    authenticationRequired?: boolean;
    prefetchedQueries?: { query: string | DocumentNode; variables?: { [key: string]: unknown } }[];
} & (
    | {
          client: Client;
          redisClient?: never;
          ssrExchange: SSRExchange;
          t?: never;
      }
    | {
          client?: never;
          redisClient: RedisClientType<RedisModules, RedisScripts>;
          ssrExchange?: SSRExchange;
          t: Translate;
      }
);

export const initServerSideProps = async ({
    domainConfig,
    context,
    redisClient,
    t,
    authenticationRequired = false,
    prefetchedQueries = [],
    client,
    ssrExchange: ssrExchangeOverride,
}: InitServerSidePropsParameters): Promise<GetServerSidePropsResult<ServerSidePropsType>> => {
    try {
        const currentSsrCache = ssrExchangeOverride ?? ssrExchange({ isClient: false });
        const currentClient =
            client ??
            (await createClient({
                ssrExchange: currentSsrCache,
                redisClient,
                context,
                t,
                publicGraphqlEndpoint: domainConfig.publicGraphqlEndpoint,
            }));

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

        if (seoPageSlug) {
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

        const slugResult = resolvedQueries.find((query) => !!query.data?.slug?.slug);
        const parsedSlug = slugResult?.data.slug.slug;

        const { trimmedUrlWithoutQueryParams, queryParams } = getServerSideInternationalizedStaticUrl(
            context,
            domainConfig.url,
        );

        const articleWithGrapesJsResult = resolvedQueries.find((query) =>
            ['BlogArticle', 'Article'].includes(query.data?.slug?.__typename),
        );

        if (articleWithGrapesJsResult) {
            const parsedCatnums = parseCatnums(articleWithGrapesJsResult.data.slug.text);
            await currentClient.query(ProductsByCatnumsDocumentApi, { catnums: parsedCatnums }).toPromise();
        }

        if (parsedSlug && parsedSlug !== trimmedUrlWithoutQueryParams) {
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
                return getUnauthenticatedRedirectSSR(getUrlWithoutGetParameters(context.resolvedUrl), domainConfig.url);
            }
        }

        const isMaintenance = resolvedQueries.some((query) => query.error?.response?.status === 503);
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
    } catch (e) {
        logException(e);
        throw e;
    }
};
