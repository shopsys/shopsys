import { Variables } from '@urql/exchange-graphcache';
import { DocumentNode } from 'graphql';
import {
    AdvertsQueryDocumentApi,
    ArticlePlacementTypeEnumApi,
    ArticlesQueryDocumentApi,
    ArticlesQueryVariablesApi,
    CurrentCustomerUserQueryDocumentApi,
    NavigationQueryDocumentApi,
    NotificationBarsDocumentApi,
    SeoPageQueryDocumentApi,
    SeoPageQueryVariablesApi,
    SettingsQueryDocumentApi,
    SettingsQueryVariablesApi,
} from 'graphql/generated';
import { getUnauthenticatedRedirectSSR } from 'helpers/auth/getUnauthenticatedRedirectSSR';
import { isUserLoggedInSSR } from 'helpers/auth/isUserLoggedInSSR';
import { getCookiesStore } from 'helpers/cookies/cookiesStoreUtils';
import { DomainConfigType } from 'helpers/domain/domainConfig';
import { getServerSideInternationalizedStaticUrl } from 'helpers/getInternationalizedStaticUrls';
import { getUrlWithoutGetParameters } from 'helpers/parsing/urlParsing';
import { extractSeoPageSlugFromUrl } from 'helpers/seo/extractSeoPageSlugFromUrl';
import { GetServerSidePropsContext, GetServerSidePropsResult } from 'next';
import { Translate } from 'next-translate';
import loadNamespaces from 'next-translate/loadNamespaces';
import { RedisClientType, RedisFunctions, RedisModules, RedisScripts } from 'redis';
import { Client, SSRData, SSRExchange, ssrExchange } from 'urql';
import { createClient } from 'urql/createClient';

export type ServerSidePropsType = {
    urqlState: SSRData;
    isMaintenance: boolean;
    domainConfig: DomainConfigType;
    cookiesStore: string | null;
};

type QueriesArray<VariablesType> = { query: string | DocumentNode; variables?: VariablesType }[];

type InitServerSidePropsParameters<VariablesType> = {
    domainConfig: DomainConfigType;
    context: GetServerSidePropsContext;
    authenticationRequired?: boolean;
    prefetchedQueries?: QueriesArray<VariablesType>;
} & (
    | {
          client: Client;
          redisClient?: never;
          ssrExchange: SSRExchange;
          t?: never;
      }
    | {
          client?: never;
          redisClient: RedisClientType<RedisModules, RedisFunctions, RedisScripts>;
          ssrExchange?: SSRExchange;
          t: Translate;
      }
);

export const initServerSideProps = async <VariablesType extends Variables>({
    domainConfig,
    context,
    redisClient,
    t,
    authenticationRequired = false,
    prefetchedQueries: additionalPrefetchQueries = [],
    client,
    ssrExchange: ssrExchangeOverride,
}: InitServerSidePropsParameters<VariablesType>): Promise<GetServerSidePropsResult<ServerSidePropsType>> => {
    const currentSsrCache = ssrExchangeOverride ?? ssrExchange({ isClient: false });
    const currentClient =
        client ??
        createClient({
            ssrExchange: currentSsrCache,
            redisClient,
            context,
            t,
            publicGraphqlEndpoint: domainConfig.publicGraphqlEndpoint,
        });

    const seoPageSlug = extractSeoPageSlugFromUrl(context.resolvedUrl, domainConfig.url);

    const prefetchQueries: QueriesArray<
        ArticlesQueryVariablesApi | SettingsQueryVariablesApi | SeoPageQueryVariablesApi | VariablesType
    > = [
        { query: NotificationBarsDocumentApi },
        { query: NavigationQueryDocumentApi },
        {
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
        },
        { query: AdvertsQueryDocumentApi },
        { query: CurrentCustomerUserQueryDocumentApi },
        { query: SettingsQueryDocumentApi },
        ...(seoPageSlug
            ? [
                  {
                      query: SeoPageQueryDocumentApi,
                      variables: {
                          pageSlug: seoPageSlug,
                      },
                  },
              ]
            : []),
        ...additionalPrefetchQueries,
    ];

    const resolvedQueries = await Promise.all(
        prefetchQueries.map((queryObject) => currentClient.query(queryObject.query, queryObject.variables).toPromise()),
    );

    const slugResult = resolvedQueries.find((query) => !!query.data?.slug?.slug);
    const parsedSlug = slugResult?.data.slug.slug;

    const { trimmedUrlWithoutQueryParams, queryParams } = getServerSideInternationalizedStaticUrl(
        context,
        domainConfig.url,
    );

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
            cookiesStore: getCookiesStore(context),
        },
    };
};
