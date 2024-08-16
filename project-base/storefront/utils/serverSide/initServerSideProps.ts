import { Variables } from '@urql/exchange-graphcache';
import { DocumentNode } from 'graphql';
import {
    AdvertsQueryDocument,
    TypeAdvertsQueryVariables,
} from 'graphql/requests/adverts/queries/AdvertsQuery.generated';
import {
    TypeArticlesQueryVariables,
    ArticlesQueryDocument,
} from 'graphql/requests/articlesInterface/articles/queries/ArticlesQuery.generated';
import { CurrentCustomerUserQueryDocument } from 'graphql/requests/customer/queries/CurrentCustomerUserQuery.generated';
import { NavigationQueryDocument } from 'graphql/requests/navigation/queries/NavigationQuery.generated';
import { NotificationBarsDocument } from 'graphql/requests/notificationBars/queries/NotificationBarsQuery.generated';
import {
    TypeSeoPageQueryVariables,
    SeoPageQueryDocument,
} from 'graphql/requests/seoPage/queries/SeoPageQuery.generated';
import {
    TypeSettingsQueryVariables,
    SettingsQueryDocument,
} from 'graphql/requests/settings/queries/SettingsQuery.generated';
import { TypeArticlePlacementTypeEnum } from 'graphql/types';
import { GetServerSidePropsContext, GetServerSidePropsResult } from 'next';
import { Translate } from 'next-translate';
import loadNamespaces from 'next-translate/loadNamespaces';
import { RedisClientType, RedisFunctions, RedisModules, RedisScripts } from 'redis';
import { CustomerUserAreaEnum, CustomerUserRoleEnum } from 'types/customer';
import { Client, SSRData, SSRExchange, ssrExchange } from 'urql';
import { createClient } from 'urql/createClient';
import { getCurrentCustomerUserRoles } from 'utils/auth/getCurrentCustomerUserRoles';
import { getIsUserAuthorizedToViewPage } from 'utils/auth/getIsUserAuthorizedToViewPage';
import { getUnauthenticatedRedirectSSR } from 'utils/auth/getUnauthenticatedRedirectSSR';
import { isUserLoggedInSSR } from 'utils/auth/isUserLoggedInSSR';
import { CookiesStoreState } from 'utils/cookies/cookiesStore';
import { DomainConfigType } from 'utils/domain/domainConfig';
import { getIsRedirectedFromSsr } from 'utils/getIsRedirectedFromSsr';
import { getUrlWithoutGetParameters } from 'utils/parsing/getUrlWithoutGetParameters';
import { extractSeoPageSlugFromUrl } from 'utils/seo/extractSeoPageSlugFromUrl';
import { getServerSideInternationalizedStaticUrl } from 'utils/staticUrls/getServerSideInternationalizedStaticUrl';

export type ServerSidePropsType = {
    urqlState: SSRData;
    isMaintenance: boolean;
    isForbidden: boolean;
    domainConfig: DomainConfigType;
    cookiesStore: CookiesStoreState;
} & Record<string, any>;

type QueriesArray<VariablesType> = { query: string | DocumentNode; variables?: VariablesType }[];

type InitServerSidePropsParameters<VariablesType> = {
    domainConfig: DomainConfigType;
    context: GetServerSidePropsContext;
    authenticationConfig?: {
        authenticationRequired?: boolean;
        authorizedRoles?: CustomerUserRoleEnum[];
        authorizedAreas?: CustomerUserAreaEnum[];
    };
    authorizedRole?: CustomerUserRoleEnum;
    prefetchedQueries?: QueriesArray<VariablesType>;
    additionalProps?: Record<string, any>;
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
    authenticationConfig = {
        authenticationRequired: false,
    },
    prefetchedQueries: additionalPrefetchQueries = [],
    client,
    ssrExchange: ssrExchangeOverride,
    additionalProps = {},
}: InitServerSidePropsParameters<VariablesType>): Promise<
    GetServerSidePropsResult<Omit<ServerSidePropsType, 'cookiesStore'>>
> => {
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
    const queriesNotToBeFetchedDuringClientSideNavigation = [
        { query: NotificationBarsDocument },
        { query: NavigationQueryDocument },
        {
            query: ArticlesQueryDocument,
            variables: {
                placement: [
                    TypeArticlePlacementTypeEnum.Footer1,
                    TypeArticlePlacementTypeEnum.Footer2,
                    TypeArticlePlacementTypeEnum.Footer3,
                    TypeArticlePlacementTypeEnum.Footer4,
                ],
                first: 100,
            },
        },
        { query: AdvertsQueryDocument, variables: { positionNames: ['header', 'footer'], categoryUuid: null } },
        { query: SettingsQueryDocument },
        ...(seoPageSlug
            ? [
                  {
                      query: SeoPageQueryDocument,
                      variables: {
                          pageSlug: seoPageSlug,
                      },
                  },
              ]
            : []),
    ];

    const isRedirectedFromSsr = getIsRedirectedFromSsr(context.req.headers);
    const prefetchQueries: QueriesArray<
        | TypeAdvertsQueryVariables
        | TypeArticlesQueryVariables
        | TypeSettingsQueryVariables
        | TypeSeoPageQueryVariables
        | VariablesType
    > = [
        { query: CurrentCustomerUserQueryDocument },
        ...(isRedirectedFromSsr ? queriesNotToBeFetchedDuringClientSideNavigation : []),
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

    if (authenticationConfig.authenticationRequired) {
        const isUserLoggedIn = isUserLoggedInSSR(currentClient);

        if (!isUserLoggedIn) {
            return getUnauthenticatedRedirectSSR(getUrlWithoutGetParameters(context.resolvedUrl), domainConfig.url);
        }
    }

    let isForbidden = false;

    if (authenticationConfig.authorizedRoles || authenticationConfig.authorizedAreas) {
        const customerUserRoles = getCurrentCustomerUserRoles(currentClient);

        const isUserAuthorized = getIsUserAuthorizedToViewPage(
            customerUserRoles,
            domainConfig.type,
            authenticationConfig.authorizedRoles,
            authenticationConfig.authorizedAreas,
        );

        if (!isUserAuthorized) {
            context.res.statusCode = 403;
            isForbidden = true;
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
            isForbidden,
            ...additionalProps,
        },
    };
};
