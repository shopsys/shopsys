import { DomainConfigType } from 'helpers/domain/domain';
import { initDomainConfig } from 'helpers/domain/initDomainConfig';
import { GetServerSideProps } from 'next';
import { RedisClientType, RedisModules, RedisScripts } from 'redis';
import { AppStore } from 'redux/main';

export const getServerSidePropsWithRedisClient =
    (
        callback: (
            redisClient: RedisClientType<any & RedisModules, RedisScripts>,
            domainConfig: DomainConfigType,
        ) => GetServerSideProps,
        store: AppStore,
    ): GetServerSideProps<any> =>
    async (context: any) => {
        const domainConfig = initDomainConfig(context, store);
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
