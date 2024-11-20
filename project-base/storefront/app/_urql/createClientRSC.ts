import { getUrqlExchanges } from './exchanges';
import { registerUrql } from '@urql/next/rsc';
import getConfig from 'next/config';
import { RedisClientType, RedisFunctions, RedisModules, RedisScripts } from 'redis';
import { Translate } from 'types/translation';
// eslint-disable-next-line no-restricted-imports
import { Client, createClient, SSRExchange } from 'urql';
import { fetcher } from 'urql/fetcher';

export const getClient = ({
    t,
    ssrExchange,
    publicGraphqlEndpoint,
    redisClient,
}: {
    t: Translate;
    ssrExchange: SSRExchange;
    publicGraphqlEndpoint: string;
    redisClient?: RedisClientType<RedisModules, RedisFunctions, RedisScripts>;
}): (() => Client) => {
    const { serverRuntimeConfig } = getConfig();
    const internalGraphqlEndpoint = serverRuntimeConfig?.internalGraphqlEndpoint ?? undefined;
    const publicGraphqlEndpointObject = new URL(publicGraphqlEndpoint);

    const makeClient = () => {
        return createClient({
            url: internalGraphqlEndpoint ?? publicGraphqlEndpoint,
            exchanges: getUrqlExchanges(ssrExchange, t),
            fetchOptions: {
                headers: {
                    OriginalHost: publicGraphqlEndpointObject.host,
                    'X-Forwarded-Proto': publicGraphqlEndpointObject.protocol === 'https:' ? 'on' : 'off',
                },
                cache: 'no-store',
            },
            fetch: fetcher(redisClient),
        });
    };
    const { getClient } = registerUrql(makeClient);

    return getClient;
};
