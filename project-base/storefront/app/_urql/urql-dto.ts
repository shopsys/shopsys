import { getClient as createClient } from 'app/_urql/createClientRSC';
import { headers } from 'next/headers';
import 'server-only';
import { ssrExchange } from 'urql';
import { getDomainConfig } from 'utils/domain/domainConfig';
import { getServerT } from 'utils/getServerTranslation';

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

export async function getUrqlData() {
    const domainConfig = getDomainConfig(headers().get('host')!);

    const publicGraphqlEndpoint = domainConfig.publicGraphqlEndpoint;

    const redisClient = await getRedis();

    await redisClient.connect();

    const client = createClient({
        publicGraphqlEndpoint,
        redisClient,
    });

    redisClient.disconnect();

    return client();
}
