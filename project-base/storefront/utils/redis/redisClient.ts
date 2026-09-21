import type { RedisClientType, RedisFunctions, RedisModules, RedisScripts } from 'redis';
import { logException } from 'utils/errors/logException';

export type AppRedisClient = RedisClientType<RedisModules, RedisFunctions, RedisScripts>;

const REDIS_RETRY_COOLDOWN_MS = 3000;

type RedisState = {
    client?: AppRedisClient;
    connecting?: Promise<void>;
    retryAfter: number;
};

type GlobalRedis = typeof globalThis & { __shopsysRedis?: RedisState };

const createAndConnectRedisClient = async (): Promise<AppRedisClient> => {
    const { createClient } = await import('redis');
    let allowReconnect = false;
    const client = createClient({
        url: `redis://${process.env.REDIS_HOST}`,
        // Requests use the backend while reconnecting instead of waiting in Redis's offline queue.
        disableOfflineQueue: true,
        socket: {
            connectTimeout: 5000,
            reconnectStrategy: (retries) =>
                !allowReconnect || retries > 10 ? false : Math.min((retries + 1) * 100, 3000),
        },
    }) as AppRedisClient;

    client.on('error', (error) => {
        // Initial connection failures are logged by the caller handling connect()'s rejection.
        if (allowReconnect) {
            logException(error);
        }
    });

    await client.connect();
    allowReconnect = true;

    return client;
};

export const getRedisClient = async (): Promise<AppRedisClient | undefined> => {
    if (typeof window !== 'undefined') {
        throw new Error('Redis client is only available on the server');
    }

    // Share the connection across server bundles and development hot reloads, never request data.
    const globalRedis = globalThis as GlobalRedis;
    globalRedis.__shopsysRedis ??= { retryAfter: 0 };
    const state = globalRedis.__shopsysRedis;

    if (state.client) {
        if (state.client.isReady) {
            return state.client;
        }

        if (!state.client.isOpen) {
            state.client = undefined;
            state.retryAfter = Date.now() + REDIS_RETRY_COOLDOWN_MS;
        }

        return undefined;
    }

    if (!state.connecting && Date.now() >= state.retryAfter) {
        // Start one connection attempt in the background; SSR can continue without caching.
        state.connecting = createAndConnectRedisClient()
            .then((client) => {
                state.client = client;
            })
            .catch((error) => {
                state.retryAfter = Date.now() + REDIS_RETRY_COOLDOWN_MS;
                logException(error);
            })
            .finally(() => {
                state.connecting = undefined;
            });
    }

    return undefined;
};
