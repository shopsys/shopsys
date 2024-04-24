import { getCookiesStoreState } from 'helpers/cookies/cookiesStoreUtils';
import { DomainConfigType, getDomainConfig } from 'helpers/domain/domainConfig';
import { GetServerSideProps, GetServerSidePropsContext } from 'next';
import { Translate } from 'next-translate';
import getT from 'next-translate/getT';
import { RedisClientType, RedisFunctions, RedisModules, RedisScripts } from 'redis';
import { CookiesStoreState } from 'store/useCookiesStore';
import { SSRExchange, ssrExchange } from 'urql';

export const getServerSidePropsWrapper =
    (
        callback: (props: {
            redisClient: RedisClientType<RedisModules, RedisFunctions, RedisScripts>;
            domainConfig: DomainConfigType;
            ssrExchange: SSRExchange;
            t: Translate;
            cookiesStoreState: CookiesStoreState;
        }) => GetServerSideProps,
    ): any =>
    async (context: GetServerSidePropsContext) => {
        const cookiesStoreState = getCookiesStoreState(context);
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
        const initServerSideProps = callback({
            redisClient,
            domainConfig,
            ssrExchange: ssrExchange({ isClient: false }),
            t,
            cookiesStoreState,
        });
        const serverSideProps = await initServerSideProps(context);

        redisClient.disconnect();

        if (!('props' in serverSideProps)) {
            return serverSideProps;
        }

        return {
            ...serverSideProps,
            props: {
                ...(await serverSideProps.props),
                cookiesStore: cookiesStoreState,
            },
        };
    };
