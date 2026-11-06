import { captureException } from '@sentry/nextjs';
import i18n from 'i18n';
import { getFreshTranslates } from 'i18n-translator';
import { type AppRedisClient, getRedisClient } from 'utils/redis/redisClient';
import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest';

vi.mock('@sentry/nextjs', () => ({ captureException: vi.fn() }));
vi.mock('envConfig', () => ({ getPublicConfigProperty: () => 'no-debug' }));
vi.mock('utils/redis/redisClient', () => ({ getRedisClient: vi.fn() }));
vi.mock('i18n-translator', () => ({ getFreshTranslates: vi.fn() }));

describe('translations using the shared Redis connection', () => {
    const translations = { greeting: 'Hello' };

    beforeEach(() => {
        vi.stubGlobal('window', undefined);
        vi.stubGlobal('fetch', vi.fn().mockResolvedValue({ ok: true }));
        vi.mocked(getFreshTranslates).mockResolvedValue(translations);
    });

    afterEach(() => {
        vi.unstubAllGlobals();
    });

    test('loads fresh translations when Redis has not connected yet', async () => {
        vi.mocked(getRedisClient).mockResolvedValue(undefined);

        expect(await i18n.loadLocaleFrom('en', 'common')).toEqual(translations);
        expect(getFreshTranslates).toHaveBeenCalledExactlyOnceWith('en', 'common');
    });

    test('keeps the shared connection open after reading cached translations', async () => {
        const client = {
            mGet: vi.fn().mockResolvedValue([JSON.stringify(translations), null]),
            disconnect: vi.fn(),
        };
        vi.mocked(getRedisClient).mockResolvedValue(client as unknown as AppRedisClient);

        expect(await i18n.loadLocaleFrom('en', 'common')).toEqual(translations);
        expect(client.mGet).toHaveBeenCalledExactlyOnceWith([
            `${process.env.REDIS_PREFIX}:fe:translates:en:common`,
            `${process.env.REDIS_PREFIX}:fe:translates:en:common/updating`,
        ]);
        expect(getFreshTranslates).not.toHaveBeenCalled();
        expect(client.disconnect).not.toHaveBeenCalled();
    });

    test('loads fresh translations if Redis fails during a cache read', async () => {
        const error = new Error('Connection lost', { cause: { code: 'EPIPE' } });
        const client = { mGet: vi.fn().mockRejectedValue(error) };
        vi.mocked(getRedisClient).mockResolvedValue(client as unknown as AppRedisClient);

        expect(await i18n.loadLocaleFrom('cs', 'accessibility')).toEqual(translations);
        expect(getFreshTranslates).toHaveBeenCalledExactlyOnceWith('cs', 'accessibility');
        expect(captureException).toHaveBeenCalledExactlyOnceWith(error, {
            extra: { location: 'i18n loadLocaleFrom' },
        });
        expect(vi.mocked(captureException).mock.calls[0][0]).toBe(error);
        expect(fetch).not.toHaveBeenCalled();
    });

    test('reports the original cache-write failure and still loads fresh translations', async () => {
        const error = new TypeError('Cache write failed');
        const client = {
            mGet: vi.fn().mockResolvedValue([null, null]),
            set: vi.fn().mockResolvedValueOnce('OK').mockRejectedValueOnce(error),
        };
        vi.mocked(getRedisClient).mockResolvedValue(client as unknown as AppRedisClient);

        expect(await i18n.loadLocaleFrom('sk', 'common')).toEqual(translations);
        expect(captureException).toHaveBeenCalledExactlyOnceWith(error, {
            extra: { location: 'i18n loadLocaleFrom' },
        });
        expect(vi.mocked(captureException).mock.calls[0][0]).toBe(error);
        expect(fetch).not.toHaveBeenCalled();
    });

    test.each(['Cache unavailable', { code: 'ECONNRESET' }])(
        'reports a non-Error rejection without serializing it: %j',
        async (reason) => {
            vi.mocked(getRedisClient).mockRejectedValueOnce(reason);

            expect(await i18n.loadLocaleFrom('en', 'common')).toEqual(translations);
            expect(captureException).toHaveBeenCalledExactlyOnceWith(reason, {
                extra: { location: 'i18n loadLocaleFrom' },
            });
            expect(fetch).not.toHaveBeenCalled();
        },
    );

    test('loads translations in the browser without Redis or server error reporting', async () => {
        vi.stubGlobal('window', {});

        expect(await i18n.loadLocaleFrom('en', 'common')).toEqual(translations);
        expect(getRedisClient).not.toHaveBeenCalled();
        expect(captureException).not.toHaveBeenCalled();
        expect(fetch).not.toHaveBeenCalled();
    });
});
