import { GetServerSidePropsContext, NextPageContext } from 'next';
import { Translate } from 'next-translate';
// eslint-disable-next-line no-restricted-imports
import { initUrqlClient } from 'next-urql';
import getConfig from 'next/config';
import { RedisClientType, RedisModules, RedisScripts } from 'redis';
import { Client, SSRExchange } from 'urql';
import { getUrqlExchanges } from 'urql/exchanges';
import { fetcher } from 'urql/fetcher';

export const createClient = ({
    t,
    ssrExchange,
    publicGraphqlEndpoint,
    redisClient,
    context,
}: {
    t: Translate;
    ssrExchange: SSRExchange;
    publicGraphqlEndpoint: string;
    redisClient?: RedisClientType<RedisModules, RedisScripts>;
    context?: GetServerSidePropsContext | NextPageContext;
}): Client => {
    const { serverRuntimeConfig } = getConfig();
    const internalGraphqlEndpoint = serverRuntimeConfig?.internalGraphqlEndpoint ?? undefined;
    const publicGraphqlEndpointObject = new URL(publicGraphqlEndpoint);

    return initUrqlClient(
        {
            url: internalGraphqlEndpoint ?? publicGraphqlEndpoint,
            exchanges: getUrqlExchanges(ssrExchange, t, context),
            fetchOptions: {
                headers: {
                    OriginalHost: publicGraphqlEndpointObject.host,
                    'X-Forwarded-Proto': publicGraphqlEndpointObject.protocol === 'https:' ? 'on' : 'off',
                },
            },
            fetch: fetcher(redisClient),
        },
        false,
    );
};
