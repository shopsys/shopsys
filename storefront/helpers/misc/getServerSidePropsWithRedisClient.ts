import { GetServerSideProps } from 'next';
import { RedisClientType, RedisModules, RedisScripts } from 'redis';

export const getServerSidePropsWithRedisClient =
    (
        callback: (redisClient: RedisClientType<any & RedisModules, RedisScripts>) => GetServerSideProps,
    ): GetServerSideProps<any> =>
    async (context: any) => {
        const createRedisClient = (await import('redis')).createClient;
        const redisClient = createRedisClient({
            url: `redis://${process.env.REDIS_HOST}`,
            socket: {
                connectTimeout: 5000,
            },
        });
        await redisClient.connect();

        const nextCallback = callback(redisClient);
        const initialProps = await nextCallback(context);

        redisClient.disconnect();

        return initialProps;
    };
