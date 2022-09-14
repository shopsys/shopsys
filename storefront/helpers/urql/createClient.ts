import { GetServerSidePropsContext } from 'next';
import { initUrqlClient, SSRExchange } from 'next-urql';
import getConfig from 'next/config';
import { AppStore } from 'redux/main';
import { Client } from 'urql';
import { getUrqlExchanges } from 'urql/exchanges';
import { fetcher } from 'urql/fetcher';

export const createClient = async (
    context: GetServerSidePropsContext,
    store: AppStore,
    ssrCache: SSRExchange,
): Promise<Client | null> => {
    const { serverRuntimeConfig } = getConfig();
    const domainConfig = store.getState().domain;
    const publicGraphqlEndpoint = new URL(domainConfig.publicGraphqlEndpoint);

    return initUrqlClient(
        {
            url: serverRuntimeConfig.internalGraphqlEndpoint,
            exchanges: getUrqlExchanges(ssrCache, context),
            fetchOptions: {
                headers: {
                    OriginalHost: publicGraphqlEndpoint.host,
                    'X-Forwarded-Proto': publicGraphqlEndpoint.protocol === 'https:' ? 'on' : 'off',
                },
            },
            fetch: fetcher(),
        },
        false,
    );
};
