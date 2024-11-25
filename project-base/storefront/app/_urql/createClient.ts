import { getUrqlExchanges } from './exchanges';
import { registerUrql } from '@urql/next/rsc';
import getConfig from 'next/config';
import { headers } from 'next/headers';
import { RedisClientType, RedisFunctions, RedisModules, RedisScripts } from 'redis';
import 'server-only';
// eslint-disable-next-line no-restricted-imports
import { Client, createClient as createUrqlClient } from 'urql';
import { fetcher } from 'urql/fetcher';
import { getDomainConfig } from 'utils/domain/domainConfig';

async function getRedis() {
    const createRedisClient = (await import('redis')).createClient;

    const redisClient = createRedisClient({
        url: `redis://${process.env.REDIS_HOST}`,
        socket: {
            connectTimeout: 5000,
        },
    });

    return redisClient;
}

function getClient({
    publicGraphqlEndpoint,
    redisClient,
}: {
    publicGraphqlEndpoint: string;
    redisClient?: RedisClientType<RedisModules, RedisFunctions, RedisScripts>;
}): () => Client {
    const { serverRuntimeConfig } = getConfig();
    const internalGraphqlEndpoint = serverRuntimeConfig?.internalGraphqlEndpoint ?? undefined;
    const publicGraphqlEndpointObject = new URL(publicGraphqlEndpoint);

    const makeClient = () => {
        return createUrqlClient({
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
}

export async function createClient() {
    const domainConfig = getDomainConfig(headers().get('host')!);

    const publicGraphqlEndpoint = domainConfig.publicGraphqlEndpoint;

    const redisClient = await getRedis();

    await redisClient.connect();

    const newClient = getClient({
        publicGraphqlEndpoint,
        redisClient,
    });

    redisClient.disconnect();

    return newClient;
}
