import type { ServerResponse } from 'http';
import type { RedisClientType, RedisFunctions, RedisModules, RedisScripts } from 'redis';
import { logException } from 'utils/errors/logException';
import { isServerTimingEnabled, recordServerTiming } from 'utils/serverSide/serverTiming';

export type AppRedisClient = RedisClientType<RedisModules, RedisFunctions, RedisScripts>;

const REDIS_RETRY_COOLDOWN_MS = 3000;
const REDIS_INITIAL_WAIT_MS = 100;

type RedisConnectionTimings = Partial<
    Record<'redis_initial_import' | 'redis_initial_create' | 'redis_initial_connect' | 'redis_initial_timeout', number>
>;

type RedisState = {
    client?: AppRedisClient;
    connecting?: Promise<void>;
    connectionWait?: Promise<AppRedisClient | undefined>;
    retryAfter: number;
    connectionTimings?: RedisConnectionTimings;
};

type GlobalRedis = typeof globalThis & { __shopsysRedis?: RedisState };

const createAndConnectRedisClient = async (timings?: RedisConnectionTimings): Promise<AppRedisClient> => {
    const importStartedAt = timings ? performance.now() : 0;
    const { createClient } = await import('redis');
    if (timings) {
        timings.redis_initial_import = performance.now() - importStartedAt;
    }

    const createStartedAt = timings ? performance.now() : 0;
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

    if (timings) {
        timings.redis_initial_create = performance.now() - createStartedAt;
    }

    const connectStartedAt = timings ? performance.now() : 0;
    try {
        await client.connect();
    } finally {
        if (timings) {
            timings.redis_initial_connect = performance.now() - connectStartedAt;
        }
    }
    allowReconnect = true;

    return client;
};

export const getRedisClient = async (
    response?: ServerResponse,
    initialWaitMs = REDIS_INITIAL_WAIT_MS,
): Promise<AppRedisClient | undefined> => {
    if (typeof window !== 'undefined') {
        throw new Error('Redis client is only available on the server');
    }

    // Share the connection across server bundles and development hot reloads, never request data.
    const globalRedis = globalThis as GlobalRedis;
    globalRedis.__shopsysRedis ??= { retryAfter: 0 };
    const state = globalRedis.__shopsysRedis;

    try {
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
            // A busy event loop can delay the timeout; a slow connection still finishes in the background.
            const timings: RedisConnectionTimings | undefined = isServerTimingEnabled() ? {} : undefined;
            state.connectionTimings = timings;
            state.connecting = createAndConnectRedisClient(timings)
                .then((client) => {
                    state.client = client;
                })
                .catch((error) => {
                    state.retryAfter = Date.now() + REDIS_RETRY_COOLDOWN_MS;
                    logException(error);
                })
                .finally(() => {
                    state.connecting = undefined;
                    state.connectionWait = undefined;
                });
            const connecting = state.connecting;
            const waitStartedAt = timings ? performance.now() : 0;
            state.connectionWait = new Promise<AppRedisClient | undefined>((resolve) => {
                const timeout = setTimeout(() => {
                    if (timings) {
                        timings.redis_initial_timeout = performance.now() - waitStartedAt;
                    }
                    resolve(undefined);
                }, initialWaitMs);
                connecting.then(() => {
                    clearTimeout(timeout);
                    resolve(state.client?.isReady ? state.client : undefined);
                });
            });
        }

        return await state.connectionWait;
    } finally {
        // Keep only numeric timings globally; never retain a response in the shared connection attempt.
        for (const [name, duration] of Object.entries(state.connectionTimings ?? {})) {
            recordServerTiming(response, name, duration);
        }
    }
};
