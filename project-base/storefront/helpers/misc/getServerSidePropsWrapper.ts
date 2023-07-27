import { DomainConfigType, getDomainConfig } from 'helpers/domain/domain';
import { GetServerSideProps } from 'next';
import { Translate } from 'next-translate';
import getT from 'next-translate/getT';
import { RedisClientType, RedisModules, RedisScripts } from 'redis';
import { SSRExchange, ssrExchange } from 'urql';

export const getServerSidePropsWrapper =
    (
        callback: (props: {
            redisClient: RedisClientType<any & RedisModules, RedisScripts>;
            domainConfig: DomainConfigType;
            ssrExchange: SSRExchange;
            t: Translate;
        }) => GetServerSideProps,
    ): any =>
    async (context: any) => {
        const domainConfig = getDomainConfig(context.req.headers.host!);
        const createRedisClient = (await import('redis')).createClient;
        const redisClient = createRedisClient({
            url: `redis://${process.env.REDIS_HOST}`,
            socket: {
                connectTimeout: 5000,
            },
        });
        await redisClient.connect();

        const t = await getT(domainConfig.defaultLocale, 'common');
        const nextCallback = callback({ redisClient, domainConfig, ssrExchange: ssrExchange({ isClient: false }), t });
        const initialProps = await nextCallback(context);

        redisClient.disconnect();

        return initialProps;
    };
