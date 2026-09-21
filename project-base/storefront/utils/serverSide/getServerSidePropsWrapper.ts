import { GetServerSideProps, GetServerSidePropsContext } from 'next';
import { Translate } from 'next-translate';
import getT from 'next-translate/getT';
import { RedisClientType, RedisFunctions, RedisModules, RedisScripts } from 'redis';
import { SSRExchange, ssrExchange } from 'urql';
import { CookiesStoreState, getCookiesStoreState } from 'utils/cookies/cookiesStore';
import { DomainConfigType, getDomainConfig } from 'utils/domain/domainConfig';
import { registerI18nConfig } from 'utils/i18n/registerI18nConfig';
import { measureServerTiming } from './serverTiming';

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
    async (context: GetServerSidePropsContext) =>
        measureServerTiming(context.res, 'gssp', async () => {
            const domainConfig = getDomainConfig(context);
            const cookiesStoreState = getCookiesStoreState(domainConfig, context);
            const createRedisClient = (await import('redis')).createClient;
            const redisClient = createRedisClient({
                url: `redis://${process.env.REDIS_HOST}`,
                socket: {
                    connectTimeout: 5000,
                },
            });
            await measureServerTiming(context.res, 'redis_connect', () => redisClient.connect());

            // next-translate/getT reads the config from globalThis; appWithI18n used to register it implicitly.
            registerI18nConfig();
            const t = await measureServerTiming(context.res, 'translations', () =>
                getT(domainConfig.defaultLocale, 'common'),
            );
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
        });
