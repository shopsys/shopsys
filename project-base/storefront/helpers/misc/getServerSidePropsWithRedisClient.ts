import { DomainConfigType, getDomainConfig } from 'helpers/domain/domain';
import { GetServerSideProps, GetServerSidePropsContext } from 'next';
import { RedisClientType, RedisModules, RedisScripts } from 'redis';

export const getServerSidePropsWithRedisClient =
    (
        callback: (
            redisClient: RedisClientType<any & RedisModules, RedisScripts>,
            domainConfig: DomainConfigType,
        ) => GetServerSideProps,
    ): GetServerSideProps<any> =>
    async (context: GetServerSidePropsContext) => {
        const domainConfig = getDomainConfig(context.req.headers.host!);
        const createRedisClient = (await import('redis')).createClient;
        const redisClient = createRedisClient({
            url: `redis://${process.env.REDIS_HOST}`,
            socket: {
                connectTimeout: 5000,
            },
        });
        await redisClient.connect();

        const nextCallback = callback(redisClient, domainConfig);
        const initialProps = await nextCallback(context);

        redisClient.disconnect();

        return initialProps;
    };
