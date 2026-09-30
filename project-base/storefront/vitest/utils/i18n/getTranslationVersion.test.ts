import { getTranslationVersion } from 'utils/i18n/getTranslationVersion';
import type { AppRedisClient } from 'utils/redis/redisClient';
import { describe, expect, test, vi } from 'vitest';

describe('translation version with optional Redis', () => {
    test('uses the default version before Redis is ready', async () => {
        const get = vi.fn();
        const client = { isReady: false, get } as unknown as AppRedisClient;

        expect(await getTranslationVersion(undefined)).toBe('0');
        expect(await getTranslationVersion(client)).toBe('0');
        expect(get).not.toHaveBeenCalled();
    });

    test('reads the current version from a ready connection', async () => {
        const get = vi.fn().mockResolvedValue('42');
        const client = { isReady: true, get } as unknown as AppRedisClient;

        expect(await getTranslationVersion(client)).toBe('42');
        expect(get).toHaveBeenCalledExactlyOnceWith(`${process.env.REDIS_PREFIX}:fe:translates:version`);
    });

    test('keeps SSR working when the connection fails during the read', async () => {
        const get = vi.fn().mockRejectedValue(new Error('Connection lost'));
        const client = { isReady: true, get } as unknown as AppRedisClient;

        expect(await getTranslationVersion(client)).toBe('0');
    });
});
