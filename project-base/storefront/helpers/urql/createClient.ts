import { GetServerSidePropsContext, NextPageContext } from 'next';
import { Translate } from 'next-translate';
import { initUrqlClient, SSRExchange } from 'next-urql';
import getConfig from 'next/config';
import { RedisClientType, RedisModules, RedisScripts } from 'redis';
import { Client } from 'urql';
import { getUrqlExchanges } from 'urql/exchanges';
import { fetcher } from 'urql/fetcher';

export const createClient = (
    t: Translate,
    ssrExchange: SSRExchange,
    publicGraphqlEndpoint: string,
    redisClient?: RedisClientType<any & RedisModules, RedisScripts>,
    context?: GetServerSidePropsContext | NextPageContext,
): Client | null => {
    const { serverRuntimeConfig } = getConfig();
    const internalGraphqlEndpoint = serverRuntimeConfig?.internalGraphqlEndpoint ?? undefined;
    const publicGraphqlEndpointObject = new URL(publicGraphqlEndpoint);
    const fetch = redisClient ? fetcher(redisClient) : undefined;

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
            fetch,
        },
        false,
    );
};
