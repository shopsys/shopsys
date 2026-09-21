import type { AppRedisClient } from 'utils/redis/redisClient';

const REDIS_TRANSLATION_VERSION_KEY = `${process.env.REDIS_PREFIX}:fe:translates:version`;
const DEFAULT_TRANSLATION_VERSION = '0';

export const getTranslationVersion = async (redisClient: AppRedisClient | undefined): Promise<string> => {
    if (!redisClient?.isReady) {
        return DEFAULT_TRANSLATION_VERSION;
    }

    try {
        return (await redisClient.get(REDIS_TRANSLATION_VERSION_KEY)) ?? DEFAULT_TRANSLATION_VERSION;
    } catch {
        return DEFAULT_TRANSLATION_VERSION;
    }
};
