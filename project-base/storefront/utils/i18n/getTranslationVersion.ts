import type { AppRedisClient } from 'utils/redis/redisClient';

const REDIS_TRANSLATION_VERSION_KEY = `${process.env.REDIS_PREFIX}:fe:translates:version`;

export const getTranslationVersion = async (redisClient: AppRedisClient | undefined): Promise<string | undefined> => {
    if (!redisClient?.isReady) {
        return undefined;
    }

    try {
        return (await redisClient.get(REDIS_TRANSLATION_VERSION_KEY)) ?? undefined;
    } catch {
        return undefined;
    }
};
