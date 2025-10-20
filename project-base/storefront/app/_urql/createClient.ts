import { getUrqlExchanges } from './exchanges';
import { registerUrql } from '@urql/next/rsc';
import { getDomainConfig } from 'app/_utils/getDomainConfig';
import {
    getExplicitPathDomainLocaleOrDefault,
    getInternalGraphqlEndpoint,
} from 'app/_utils/getInternalGraphqlEndpoint';
import getConfig from 'next/config';
import { cookies } from 'next/headers';
import { RedisClientType, RedisFunctions, RedisModules, RedisScripts } from 'redis';
import 'server-only';
// eslint-disable-next-line no-restricted-imports
import { Client, createClient as createUrqlClient } from 'urql';
import { fetcher } from 'urql/fetcher';

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

async function getClient({
    publicGraphqlEndpoint,
    domainUrl,
    redisClient,
}: {
    publicGraphqlEndpoint: string;
    domainUrl: string;
    redisClient?: RedisClientType<RedisModules, RedisFunctions, RedisScripts>;
}): Promise<() => Client> {
    const { serverRuntimeConfig } = getConfig();
    const locale = getExplicitPathDomainLocaleOrDefault(domainUrl);
    const internalGraphqlEndpoint = getInternalGraphqlEndpoint(serverRuntimeConfig?.internalGraphqlEndpoint, locale);
    const publicGraphqlEndpointObject = new URL(publicGraphqlEndpoint);
    const accessToken = (await cookies()).get('accessToken')?.value;

    const makeClient = () => {
        const finalUrl = internalGraphqlEndpoint ?? publicGraphqlEndpoint;

        return createUrqlClient({
            url: finalUrl,
            exchanges: getUrqlExchanges(accessToken),
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
    const domainConfig = await getDomainConfig();

    const publicGraphqlEndpoint = domainConfig.publicGraphqlEndpoint;
    const domainUrl = domainConfig.url;

    const redisClient = await getRedis();

    // await redisClient.connect();

    const newClient = await getClient({
        publicGraphqlEndpoint,
        domainUrl,
        redisClient,
    });

    // redisClient.disconnect();

    return newClient;
}
