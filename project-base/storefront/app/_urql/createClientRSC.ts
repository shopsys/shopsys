import { getUrqlExchanges } from './exchanges';
import { registerUrql } from '@urql/next/rsc';
import getConfig from 'next/config';
import { RedisClientType, RedisFunctions, RedisModules, RedisScripts } from 'redis';
// eslint-disable-next-line no-restricted-imports
import { Client, createClient } from 'urql';
import { fetcher } from 'urql/fetcher';

export const getClient = ({
    publicGraphqlEndpoint,
    redisClient,
}: {
    publicGraphqlEndpoint: string;
    redisClient?: RedisClientType<RedisModules, RedisFunctions, RedisScripts>;
}): (() => Client) => {
    const { serverRuntimeConfig } = getConfig();
    const internalGraphqlEndpoint = serverRuntimeConfig?.internalGraphqlEndpoint ?? undefined;
    const publicGraphqlEndpointObject = new URL(publicGraphqlEndpoint);

    const makeClient = () => {
        return createClient({
            url: internalGraphqlEndpoint ?? publicGraphqlEndpoint,
            exchanges: getUrqlExchanges(),
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
