import { createClient } from 'redis';
import { logException } from 'utils/errors/logException';
import { getRedisClient } from 'utils/redis/redisClient';
import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest';

vi.mock('redis', () => ({ createClient: vi.fn() }));
vi.mock('utils/errors/logException', () => ({ logException: vi.fn() }));

describe('shared Redis connection', () => {
    let finishConnecting: () => void;
    let failConnecting: (error: Error) => void;
    let now: number;
    const client = {
        isReady: false,
        isOpen: false,
        connect: vi.fn(),
        on: vi.fn(),
    };

    beforeEach(() => {
        vi.stubGlobal('window', undefined);
        vi.stubGlobal('__shopsysRedis', undefined);
        now = 10000;
        vi.spyOn(Date, 'now').mockImplementation(() => now);
        client.isReady = false;
        client.isOpen = false;
        client.connect.mockImplementation(() => {
            client.isOpen = true;
            return new Promise<void>((resolve, reject) => {
                finishConnecting = () => {
                    client.isReady = true;
                    resolve();
                };
                failConnecting = (error) => {
                    client.isOpen = false;
                    reject(error);
                };
            });
        });
        vi.mocked(createClient).mockReturnValue(client as unknown as ReturnType<typeof createClient>);
    });

    afterEach(() => {
        vi.unstubAllGlobals();
    });

    const connectInBackground = async () => {
        expect(await getRedisClient()).toBeUndefined();
        await vi.waitFor(() => expect(client.connect).toHaveBeenCalledTimes(1));
        finishConnecting();
        await vi.waitFor(async () => expect(await getRedisClient()).toBe(client));
    };

    test('concurrent requests continue without cache while a single connection starts', async () => {
        const results = await Promise.all(Array.from({ length: 10 }, () => getRedisClient()));

        expect(results).toEqual(Array(10).fill(undefined));
        await vi.waitFor(() => expect(client.connect).toHaveBeenCalledTimes(1));
        expect(createClient).toHaveBeenCalledTimes(1);
        finishConnecting();
        await vi.waitFor(async () => expect(await getRedisClient()).toBe(client));
    });

    test('reuses the ready connection across requests and module reloads', async () => {
        await connectInBackground();
        vi.resetModules();
        const reloadedModule = await import('utils/redis/redisClient');

        expect(await reloadedModule.getRedisClient()).toBe(client);
        expect(await getRedisClient()).toBe(client);
        expect(client.connect).toHaveBeenCalledTimes(1);
    });

    test('retries a failed initial connection after a cooldown, without blocking requests', async () => {
        const error = new Error('Redis unavailable');
        await getRedisClient();
        await vi.waitFor(() => expect(client.connect).toHaveBeenCalledTimes(1));
        failConnecting(error);
        await vi.waitFor(() => expect(logException).toHaveBeenCalledWith(error));

        now += 2999;
        expect(await getRedisClient()).toBeUndefined();
        expect(createClient).toHaveBeenCalledTimes(1);
        now += 1;
        expect(await getRedisClient()).toBeUndefined();
        await vi.waitFor(() => expect(client.connect).toHaveBeenCalledTimes(2));
        finishConnecting();
        await vi.waitFor(async () => expect(await getRedisClient()).toBe(client));
    });

    test('bypasses a reconnecting client and reuses it once ready', async () => {
        await connectInBackground();
        client.isReady = false;

        expect(await getRedisClient()).toBeUndefined();
        expect(client.connect).toHaveBeenCalledTimes(1);
        client.isReady = true;
        expect(await getRedisClient()).toBe(client);
        expect(createClient).toHaveBeenCalledTimes(1);
    });

    test('starts a new connection after exhausted reconnect attempts and a cooldown', async () => {
        await connectInBackground();
        client.isReady = false;
        client.isOpen = false;

        expect(await getRedisClient()).toBeUndefined();
        now += 2999;
        expect(await getRedisClient()).toBeUndefined();
        expect(createClient).toHaveBeenCalledTimes(1);
        now += 1;
        expect(await getRedisClient()).toBeUndefined();
        await vi.waitFor(() => expect(client.connect).toHaveBeenCalledTimes(2));
        finishConnecting();
        await vi.waitFor(async () => expect(await getRedisClient()).toBe(client));
    });

    test('disables offline queuing and bounds automatic reconnects after the initial connection', async () => {
        await getRedisClient();
        await vi.waitFor(() => expect(client.connect).toHaveBeenCalledTimes(1));
        const options = vi.mocked(createClient).mock.calls[0][0];
        const reconnectStrategy = options?.socket?.reconnectStrategy;
        expect(options?.disableOfflineQueue).toBe(true);
        if (typeof reconnectStrategy !== 'function') {
            throw new Error('Expected a reconnect strategy');
        }
        const error = new Error('Connection lost');
        expect(reconnectStrategy(0, error)).toBe(false);
        finishConnecting();
        await vi.waitFor(async () => expect(await getRedisClient()).toBe(client));

        expect(reconnectStrategy(0, error)).toBe(100);
        expect(reconnectStrategy(10, error)).toBe(1100);
        expect(reconnectStrategy(11, error)).toBe(false);
    });
});
