import { initUrqlClient, SSRExchange } from 'next-urql';
import { AppStore } from 'redux/main';
import { Client } from 'urql';
import getConfig from 'next/config';
import { GetServerSidePropsContext } from 'next';
import { getUrqlExchanges } from 'urql/exchanges';

export const createClient = (
    context: GetServerSidePropsContext,
    store: AppStore,
    ssrCache: SSRExchange,
): Client | null => {
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
        },
        false,
    );
};
