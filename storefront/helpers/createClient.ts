import { isServer } from './isServer';
import { GetServerSidePropsContext } from 'next';
import { initUrqlClient, SSRExchange } from 'next-urql';
import getConfig from 'next/config';
import { AppStore } from 'redux/main';
import { Client } from 'urql';
import { getUrqlExchanges } from 'urql/exchanges';
import { fetcher } from 'urql/fetcher';

const REDIS_URL = `redis://${process.env.REDIS_HOST}`;

export const createClient = async (
    context: GetServerSidePropsContext,
    store: AppStore,
    ssrCache: SSRExchange,
): Promise<Client | null> => {
    const { serverRuntimeConfig } = getConfig();
    const domainConfig = store.getState().domain;
    const publicGraphqlEndpoint = new URL(domainConfig.publicGraphqlEndpoint);

    const getRedisClient = async () => {
        if (isServer()) {
            const createRedisClient = (await import('redis')).createClient;

            const client = createRedisClient({
                url: REDIS_URL,
                socket: {
                    connectTimeout: 5000,
                },
            });

            await client.connect();
        }

        return null;
    };

    return initUrqlClient(
        {
            url: serverRuntimeConfig.internalGraphqlEndpoint,
            exchanges: getUrqlExchanges(ssrCache, context),
            fetchOptions: {
                headers: {
                    OriginalHost: publicGraphqlEndpoint.host,
                    'X-Forwarded-Proto': publicGraphqlEndpoint.protocol === 'https:' ? 'on' : 'off',
                },
            },
            fetch: fetcher(await getRedisClient()),
        },
        false,
    );
};
