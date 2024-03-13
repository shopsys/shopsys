import { DomainConfigType, getDomainConfig } from 'helpers/domain/domainConfig';
import { GetServerSideProps, GetServerSidePropsContext } from 'next';
import { Translate } from 'next-translate';
import getT from 'next-translate/getT';
import { RedisClientType, RedisFunctions, RedisModules, RedisScripts } from 'redis';
import { SSRExchange, ssrExchange } from 'urql';

export const getServerSidePropsWrapper =
    (
        callback: (props: {
            redisClient: RedisClientType<RedisModules, RedisFunctions, RedisScripts>;
            domainConfig: DomainConfigType;
            ssrExchange: SSRExchange;
            t: Translate;
        }) => GetServerSideProps,
    ): any =>
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

        const t = await getT(domainConfig.defaultLocale, 'common');
        const nextCallback = callback({
            redisClient,
            domainConfig,
            ssrExchange: ssrExchange({ isClient: false }),
            t,
        });
        const initialProps = await nextCallback(context);

        redisClient.disconnect();

        return initialProps;
    };
