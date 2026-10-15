import * as Sentry from '@sentry/nextjs';
import { IncomingMessage, ServerResponse } from 'http';
import { onRequestError, register } from 'instrumentation';
import { Socket } from 'net';
import { PHASE_PRODUCTION_BUILD, PHASE_PRODUCTION_SERVER } from 'next/constants';
import { createClient } from 'redis';
import { logException } from 'utils/errors/logException';
import { getRedisClient } from 'utils/redis/redisClient';
import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest';

vi.mock('redis', () => ({ createClient: vi.fn() }));
vi.mock('utils/errors/logException', () => ({ logException: vi.fn() }));
vi.mock('@sentry/nextjs', () => ({ init: vi.fn(), captureRequestError: vi.fn() }));

describe('Redis startup instrumentation', () => {
    let finishConnecting: () => void;
    let failConnecting: (error: Error) => void;
    const client = { isReady: false, isOpen: false, connect: vi.fn(), on: vi.fn() };

    beforeEach(() => {
        vi.useFakeTimers();
        vi.stubGlobal('window', undefined);
        vi.stubGlobal('__shopsysRedis', undefined);
        vi.stubEnv('NEXT_RUNTIME', 'nodejs');
        vi.stubEnv('NEXT_PHASE', PHASE_PRODUCTION_SERVER);
        vi.stubEnv('REDIS_HOST', 'redis:6379');
        vi.stubEnv('SENTRY_DSN', '');
        vi.stubEnv('SERVER_TIMING', '1');
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
        vi.useRealTimers();
        vi.unstubAllGlobals();
        vi.unstubAllEnvs();
    });

    test.each([
        '',
        'https://public@example.invalid/1',
    ])('waits beyond the request timeout and reuses the warmed client with Sentry DSN "%s"', async (dsn) => {
        vi.stubEnv('SENTRY_DSN', dsn);
        vi.stubEnv('INTERNAL_ENDPOINT', 'http://backend/');
        const completed = vi.fn();

        const startup = register().then(completed);
        await vi.dynamicImportSettled();
        await vi.advanceTimersByTimeAsync(800);
        expect(completed).not.toHaveBeenCalled();
        finishConnecting();
        await startup;

        const response = new ServerResponse(new IncomingMessage(new Socket()));
        expect(await getRedisClient(response)).toBe(client);
        expect(createClient).toHaveBeenCalledOnce();
        expect(client.connect).toHaveBeenCalledOnce();
        expect(response.getHeader('Server-Timing')).toContain('redis_initial_connect;dur=');
        expect(response.getHeader('Server-Timing')).not.toContain('redis_initial_timeout');
        expect(vi.getTimerCount()).toBe(0);
        expect(Sentry.init).toHaveBeenCalledTimes(dsn ? 1 : 0);
        if (dsn) {
            expect(Sentry.init).toHaveBeenCalledWith(
                expect.objectContaining({
                    dsn,
                    tracesSampleRate: 0.1,
                    tracePropagationTargets: [expect.any(RegExp)],
                }),
            );
        }
        expect(onRequestError).toBe(Sentry.captureRequestError);
    });

    test('allows startup after two seconds and reuses a connection completed in the background', async () => {
        const completed = vi.fn();
        const startup = register().then(completed);
        await vi.dynamicImportSettled();
        await vi.advanceTimersByTimeAsync(1999);
        expect(completed).not.toHaveBeenCalled();

        await vi.advanceTimersByTimeAsync(1);
        await startup;

        expect(completed).toHaveBeenCalledOnce();
        expect(await getRedisClient()).toBeUndefined();
        expect(vi.getTimerCount()).toBe(0);
        finishConnecting();
        await vi.advanceTimersByTimeAsync(0);
        expect(await getRedisClient()).toBe(client);
        expect(createClient).toHaveBeenCalledOnce();
    });

    test('allows startup after a connection failure and preserves retry on a later request', async () => {
        const error = new Error('Redis unavailable');
        const startup = register();
        await vi.dynamicImportSettled();

        failConnecting(error);
        await expect(startup).resolves.toBeUndefined();

        expect(logException).toHaveBeenCalledWith(error);
        expect(await getRedisClient()).toBeUndefined();
        expect(vi.getTimerCount()).toBe(0);
        await vi.advanceTimersByTimeAsync(3000);
        const request = getRedisClient();
        await vi.dynamicImportSettled();
        finishConnecting();
        expect(await request).toBe(client);
        expect(createClient).toHaveBeenCalledTimes(2);
    });

    test.each([
        ['edge', PHASE_PRODUCTION_SERVER, 'redis:6379'],
        ['nodejs', PHASE_PRODUCTION_BUILD, 'redis:6379'],
        ['nodejs', PHASE_PRODUCTION_SERVER, ''],
    ])('skips Redis for runtime=%s, phase=%s, host="%s"', async (runtime, phase, host) => {
        vi.stubEnv('NEXT_RUNTIME', runtime);
        vi.stubEnv('NEXT_PHASE', phase);
        vi.stubEnv('REDIS_HOST', host);
        vi.stubEnv('SENTRY_DSN', 'https://public@example.invalid/1');

        await register();

        expect(createClient).not.toHaveBeenCalled();
        expect(Sentry.init).toHaveBeenCalledOnce();
        expect(vi.getTimerCount()).toBe(0);
    });
});
