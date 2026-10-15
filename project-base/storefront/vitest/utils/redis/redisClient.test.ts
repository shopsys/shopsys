import { IncomingMessage, ServerResponse } from 'http';
import { Socket } from 'net';
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
        vi.stubEnv('SERVER_TIMING', '0');
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
        vi.unstubAllEnvs();
    });

    const connectInBackground = async () => {
        expect(await getRedisClient()).toBeUndefined();
        await vi.waitFor(() => expect(client.connect).toHaveBeenCalledTimes(1));
        finishConnecting();
        await vi.waitFor(async () => expect(await getRedisClient()).toBe(client));
    };

    test('concurrent requests fall back after the shared initial wait expires', async () => {
        const results = await Promise.all(Array.from({ length: 10 }, () => getRedisClient()));

        expect(results).toEqual(Array(10).fill(undefined));
        await vi.waitFor(() => expect(client.connect).toHaveBeenCalledTimes(1));
        expect(createClient).toHaveBeenCalledTimes(1);
        finishConnecting();
        await vi.waitFor(async () => expect(await getRedisClient()).toBe(client));
    });

    test('concurrent requests use a connection that becomes ready during the initial wait', async () => {
        const requests = Promise.all(Array.from({ length: 10 }, () => getRedisClient()));
        await vi.waitFor(() => expect(client.connect).toHaveBeenCalledTimes(1));
        finishConnecting();

        expect(await requests).toEqual(Array(10).fill(client));
        expect(createClient).toHaveBeenCalledTimes(1);
    });

    test('limits the shared initial wait to 100 ms without cancelling the connection', async () => {
        vi.useFakeTimers();
        try {
            const completed = vi.fn();
            const request = getRedisClient().then(completed);
            await vi.advanceTimersByTimeAsync(99);
            expect(completed).not.toHaveBeenCalled();

            await vi.advanceTimersByTimeAsync(1);
            await request;
            expect(completed).toHaveBeenCalledWith(undefined);

            finishConnecting();
            await vi.advanceTimersByTimeAsync(0);
            expect(await getRedisClient()).toBe(client);
            expect(createClient).toHaveBeenCalledTimes(1);
        } finally {
            vi.useRealTimers();
        }
    });

    test('later requests do not wait again for an initial connection that already timed out', async () => {
        await getRedisClient();
        vi.useFakeTimers();
        try {
            expect(await getRedisClient()).toBeUndefined();
            expect(vi.getTimerCount()).toBe(0);
            finishConnecting();
        } finally {
            vi.useRealTimers();
        }
    });

    test('reuses the ready connection across requests and module reloads', async () => {
        await connectInBackground();
        vi.resetModules();
        const reloadedModule = await import('utils/redis/redisClient');

        expect(await reloadedModule.getRedisClient()).toBe(client);
        expect(await getRedisClient()).toBe(client);
        expect(client.connect).toHaveBeenCalledTimes(1);
    });

    test('retries a failed initial connection after a cooldown with a bounded wait', async () => {
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

    test('reports shared connection phases on each response without repeating the connection', async () => {
        vi.stubEnv('SERVER_TIMING', '1');
        const firstResponse = new ServerResponse(new IncomingMessage(new Socket()));
        const secondResponse = new ServerResponse(new IncomingMessage(new Socket()));
        firstResponse.setHeader('Server-Timing', 'upstream;dur=2');

        const requests = Promise.all([getRedisClient(firstResponse), getRedisClient(secondResponse)]);
        await vi.waitFor(() => expect(client.connect).toHaveBeenCalledOnce());
        finishConnecting();
        expect(await requests).toEqual([client, client]);

        const phases = secondResponse.getHeader('Server-Timing');
        expect(phases).toMatch(
            /^redis_initial_import;dur=\d+\.\d, redis_initial_create;dur=\d+\.\d, redis_initial_connect;dur=\d+\.\d$/,
        );
        expect(firstResponse.getHeader('Server-Timing')).toBe(`upstream;dur=2, ${phases}`);
        const warmResponse = new ServerResponse(new IncomingMessage(new Socket()));
        expect(await getRedisClient(warmResponse)).toBe(client);
        expect(warmResponse.getHeader('Server-Timing')).toBe(phases);
        expect(createClient).toHaveBeenCalledOnce();

        vi.stubEnv('SERVER_TIMING', '0');
        const disabledResponse = new ServerResponse(new IncomingMessage(new Socket()));
        expect(await getRedisClient(disabledResponse)).toBe(client);
        expect(disabledResponse.getHeader('Server-Timing')).toBeUndefined();
    });

    test('reports the actual timeout delay and preserves connection timings completed in the background', async () => {
        vi.stubEnv('SERVER_TIMING', '1');
        vi.useFakeTimers();
        let elapsed = 0;
        vi.spyOn(performance, 'now').mockImplementation(() => elapsed);
        const firstResponse = new ServerResponse(new IncomingMessage(new Socket()));
        try {
            const request = getRedisClient(firstResponse);
            await vi.advanceTimersByTimeAsync(0);
            elapsed = 737;
            await vi.advanceTimersByTimeAsync(100);
            expect(await request).toBeUndefined();
            const firstHeader = firstResponse.getHeader('Server-Timing');
            expect(firstHeader).toContain('redis_initial_timeout;dur=737.0');
            expect(firstHeader).not.toContain('redis_initial_connect');

            elapsed = 900;
            finishConnecting();
            await vi.advanceTimersByTimeAsync(0);
            const nextResponse = new ServerResponse(new IncomingMessage(new Socket()));
            expect(await getRedisClient(nextResponse)).toBe(client);
            expect(nextResponse.getHeader('Server-Timing')).toContain('redis_initial_connect;dur=900.0');
            expect(nextResponse.getHeader('Server-Timing')).toContain('redis_initial_timeout;dur=737.0');
            expect(firstResponse.getHeader('Server-Timing')).toBe(firstHeader);
        } finally {
            vi.useRealTimers();
        }
    });

    test('does not emit connection timings when diagnostics are disabled', async () => {
        const response = new ServerResponse(new IncomingMessage(new Socket()));

        const request = getRedisClient(response);
        await vi.waitFor(() => expect(client.connect).toHaveBeenCalledOnce());
        finishConnecting();

        expect(await request).toBe(client);
        expect(response.getHeader('Server-Timing')).toBeUndefined();
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
