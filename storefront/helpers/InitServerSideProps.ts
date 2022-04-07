import { GetServerSidePropsContext, GetServerSidePropsResult } from 'next';
import { initUrqlClient, SSRData } from 'next-urql';
import { AdvertsQueryDocumentApi, NavigationQueryDocumentApi, NotificationBarsDocumentApi } from 'graphql/generated';
import { AppStore } from 'redux/main';
import { DocumentNode } from 'graphql';
import getConfig from 'next/config';
import { getUrqlExchanges } from 'urql/exchanges';
import { hasTokenInCookie } from 'utils/Auth/TokensFromCookies';
import nextI18NextConfig from 'next-i18next.config';
import { serverSideTranslations } from 'next-i18next/serverSideTranslations';
import { SSRConfig } from 'next-i18next';
import { ssrExchange } from '@urql/core';
import { userActions } from 'redux/slices/user';

export type ServerSidePropsType = {
    urqlState: SSRData;
} & SSRConfig;

export async function initServerSideProps(
    context: GetServerSidePropsContext,
    store: AppStore,
    prefetchedQueries: { query: string | DocumentNode; variables?: { [key: string]: unknown } }[] = [],
): Promise<GetServerSidePropsResult<ServerSidePropsType>> {
    store.dispatch(userActions.setIsUserLoggedIn(hasTokenInCookie(context)));

    const domainConfig = store.getState().domain;
    const { serverRuntimeConfig } = getConfig();
    const ssrCache = ssrExchange({ isClient: false });

    const publicGraphqlEndpoint = new URL(domainConfig.publicGraphqlEndpoint);
    const client = initUrqlClient(
        {
            url: serverRuntimeConfig.internalGraphqlEndpoint,
            exchanges: getUrqlExchanges(ssrCache, context),
            fetchOptions: {
                headers: {
                    OriginalHost: publicGraphqlEndpoint.host,
                    'X-Forwarded-Proto': publicGraphqlEndpoint.protocol === 'https:' ? 'on' : 'off',
                },
            },
        },
        false,
    );

    let serversideTranslationConfig;

    if (client !== null) {
        serversideTranslationConfig = await serverSideTranslations(
            domainConfig.defaultLocale,
            undefined,
            nextI18NextConfig,
        );

        prefetchedQueries.push({ query: NotificationBarsDocumentApi });
        prefetchedQueries.push({ query: NavigationQueryDocumentApi });
        prefetchedQueries.push({ query: AdvertsQueryDocumentApi });

        const resolvedQueries = await Promise.all(
            prefetchedQueries.map((queryObject) => client.query(queryObject.query, queryObject.variables).toPromise()),
        );
        const slugResult = resolvedQueries.find((query) => query.data?.slug?.slug !== undefined);
        const parsedSlug = slugResult?.data.slug.slug;
        const trimmedUrl = context.resolvedUrl.split('?')[0];

        if (parsedSlug !== undefined && parsedSlug !== trimmedUrl) {
            return {
                redirect: {
                    statusCode: 301,
                    destination: parsedSlug,
                },
            };
        }

        return {
            props: {
                ...serversideTranslationConfig,
                urqlState: ssrCache.extractData(),
            },
        };
    }
    return { props: <ServerSidePropsType>{} };
}
