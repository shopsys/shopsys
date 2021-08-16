import { cacheExchange, dedupExchange, fetchExchange, ssrExchange } from 'urql';
import { GetServerSidePropsContext, GetServerSidePropsResult } from 'next';
import getConfig from 'next/config';
import { initUrqlClient } from 'next-urql';
import { serverSideTranslations } from 'next-i18next/serverSideTranslations';

export async function initServerSideProps(
    context: GetServerSidePropsContext,
    prefetchedQueries: string[] = [],
): Promise<GetServerSidePropsResult<{ [key: string]: any }>> {
    const { publicRuntimeConfig, serverRuntimeConfig } = getConfig();
    const ssrCache = ssrExchange({ isClient: false });
    const client = initUrqlClient(
        {
            url: serverRuntimeConfig.internalGraphqlEndpoint,
            exchanges: [dedupExchange, cacheExchange, ssrCache, fetchExchange],
            fetchOptions: {
                headers: {
                    OriginalHost: new URL(publicRuntimeConfig.publicGraphqlEndpoint).host,
                },
            },
        },
        false,
    );

    let serversideTranslationConfig;

    if (context.defaultLocale !== undefined && client !== null) {
        serversideTranslationConfig = await serverSideTranslations(context.defaultLocale);

        for (const query of prefetchedQueries) {
            await client.query(query).toPromise();
        }

        return {
            props: {
                ...serversideTranslationConfig,
                urqlState: ssrCache.extractData(),
            },
        };
    }
    return { props: {} };
}
