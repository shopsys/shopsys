import { GetServerSidePropsContext, NextPageContext } from 'next';
import { initUrqlClient, SSRExchange } from 'next-urql';
import getConfig from 'next/config';
import { RedisClientType, RedisModules, RedisScripts } from 'redis';
import { Client } from 'urql';
import { getUrqlExchanges } from 'urql/exchanges';
import { fetcher } from 'urql/fetcher';

export const createClient = (
    context: GetServerSidePropsContext | NextPageContext,
    publicGraphqlEndpoint: string,
    ssrCache: SSRExchange,
    redisClient: RedisClientType<any & RedisModules, RedisScripts>,
): Client | null => {
    const { serverRuntimeConfig } = getConfig();
    const graphqlEndpoint = new URL(publicGraphqlEndpoint);
    const fetch = fetcher(redisClient);

    return initUrqlClient(
        {
            url: serverRuntimeConfig.internalGraphqlEndpoint,
            exchanges: getUrqlExchanges(ssrCache, context),
            fetchOptions: {
                headers: {
                    OriginalHost: graphqlEndpoint.host,
                    'X-Forwarded-Proto': graphqlEndpoint.protocol === 'https:' ? 'on' : 'off',
                },
            },
            fetch,
        },
        false,
    );
};
