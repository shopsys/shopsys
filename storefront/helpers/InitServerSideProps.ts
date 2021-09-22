import { cacheExchange, dedupExchange, fetchExchange, ssrExchange } from 'urql';
import { DomainConfigType, getDomainConfig } from 'utils/Domain/Domain';
import { GetServerSidePropsContext, GetServerSidePropsResult } from 'next';
import { initUrqlClient, SSRData } from 'next-urql';
import getConfig from 'next/config';
import nextI18NextConfig from 'next-i18next.config';
import { serverSideTranslations } from 'next-i18next/serverSideTranslations';
import { SSRConfig } from 'next-i18next';

export type ServerSidePropsType = {
    urqlState: SSRData;
    domainConfig: DomainConfigType;
} & SSRConfig;

export async function initServerSideProps(
    context: GetServerSidePropsContext,
    prefetchedQueries: string[] = [],
): Promise<GetServerSidePropsResult<ServerSidePropsType>> {
    const domain = context.req.headers.host;
    const domainConfig = getDomainConfig(domain);
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

        await Promise.all(prefetchedQueries.map((query) => client.query(query).toPromise()));

        return {
            props: {
                ...serversideTranslationConfig,
                urqlState: ssrCache.extractData(),
                domainConfig: domainConfig,
            },
        };
    }
    return { props: <ServerSidePropsType>{} };
}
