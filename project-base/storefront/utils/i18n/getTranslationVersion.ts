import { RedisClientType, RedisFunctions, RedisModules, RedisScripts } from 'redis';

const REDIS_TRANSLATION_VERSION_KEY = `${process.env.REDIS_PREFIX}:fe:translates:version`;
const DEFAULT_TRANSLATION_VERSION = '0';

type TranslationVersionRedisClient = RedisClientType<RedisModules, RedisFunctions, RedisScripts>;

export const getTranslationVersion = async (redisClient: TranslationVersionRedisClient): Promise<string> => {
    try {
        return (await redisClient.get(REDIS_TRANSLATION_VERSION_KEY)) ?? DEFAULT_TRANSLATION_VERSION;
    } catch {
        return DEFAULT_TRANSLATION_VERSION;
    }
};
