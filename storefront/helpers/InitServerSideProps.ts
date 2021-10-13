import { cacheExchange, dedupExchange, fetchExchange, ssrExchange } from 'urql';
import { GetServerSidePropsContext, GetServerSidePropsResult } from 'next';
import { initUrqlClient, SSRData } from 'next-urql';
import { AppStore } from 'redux/main';
import { cartInputActions } from 'redux/slices/cartInput';
import { DocumentNode } from 'graphql';
import { getCartInputCookie } from './Cookies';
import getConfig from 'next/config';
import nextI18NextConfig from 'next-i18next.config';
import { serverSideTranslations } from 'next-i18next/serverSideTranslations';
import { SSRConfig } from 'next-i18next';

export type ServerSidePropsType = {
    urqlState: SSRData;
} & SSRConfig;

export async function initServerSideProps(
    context: GetServerSidePropsContext,
    store: AppStore,
    prefetchedQueries: (string | DocumentNode)[] = [],
): Promise<GetServerSidePropsResult<ServerSidePropsType>> {
    store.dispatch(cartInputActions.setCartInputData(getCartInputCookie(context)));
    const domainConfig = store.getState().domain;
    const { serverRuntimeConfig } = getConfig();
    const ssrCache = ssrExchange({ isClient: false });

    const publicGraphqlEndpoint = new URL(domainConfig.publicGraphqlEndpoint);
    const client = initUrqlClient(
        {
            url: serverRuntimeConfig.internalGraphqlEndpoint,
            exchanges: [dedupExchange, cacheExchange, ssrCache, fetchExchange],
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

    if (domainConfig.defaultLocale !== undefined && client !== null) {
        serversideTranslationConfig = await serverSideTranslations(
            domainConfig.defaultLocale,
            undefined,
            nextI18NextConfig,
        );

        const resolvedQueries = await Promise.all(prefetchedQueries.map((query) => client.query(query).toPromise()));
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
