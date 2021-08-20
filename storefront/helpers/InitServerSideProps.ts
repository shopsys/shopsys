import { cacheExchange, dedupExchange, fetchExchange, ssrExchange } from 'urql';
import { GetServerSidePropsContext, GetServerSidePropsResult } from 'next';
import getConfig from 'next/config';
import { getDomainConfig } from '../utils/Domain/Domain';
import { initUrqlClient } from 'next-urql';
import nextI18NextConfig from '../next-i18next.config';
import { serverSideTranslations } from 'next-i18next/serverSideTranslations';

export async function initServerSideProps(
    context: GetServerSidePropsContext,
    prefetchedQueries: string[] = [],
): Promise<GetServerSidePropsResult<{ [key: string]: any }>> {
    const domain = context.req.headers.host;
    let domainConfig;
    if (domain !== undefined) {
        domainConfig = getDomainConfig(domain);
    }
    const { publicRuntimeConfig, serverRuntimeConfig } = getConfig();
    const ssrCache = ssrExchange({ isClient: false });
    const client = initUrqlClient(
        {
            url: serverRuntimeConfig.internalGraphqlEndpoint,
            exchanges: [dedupExchange, cacheExchange, ssrCache, fetchExchange],
            fetchOptions: {
                headers: {
                    OriginalHost: new URL(publicRuntimeConfig.publicGraphqlEndpoint).host,
                    'X-Forwarded-Proto':
                        new URL(publicRuntimeConfig.publicGraphqlEndpoint).protocol === 'https:' ? 'on' : 'off',
                },
            },
        },
        false,
    );

    let serversideTranslationConfig;

    if (context.defaultLocale !== undefined && client !== null) {
        serversideTranslationConfig = await serverSideTranslations(context.defaultLocale, undefined, nextI18NextConfig);

        for (const query of prefetchedQueries) {
            await client.query(query).toPromise();
        }

        return {
            props: {
                ...serversideTranslationConfig,
                urqlState: ssrCache.extractData(),
                domainConfig: domainConfig,
            },
        };
    }
    return { props: {} };
}
