const REDIS_PREFIX = `${process.env.REDIS_PREFIX}:fe:translates:`;
const REDIS_UPDATE_JOB_TIMEOUT = 5; // seconds (default: 30)

const logException = async (error) => {
    if (typeof window === 'undefined') {
        const { logException: logServerException } = await import('./utils/errors/logException');
        logServerException(error, { location: 'i18n loadLocaleFrom' });
    }
};

module.exports = {
    pages: {
        '*': ['common', 'accessibility'],
    },
    locales: ['default', 'en', 'cs', 'sk'],
    defaultLocale: 'default',
    localeDetection: false,
    serializeConfig: false,
    defaultNS: 'common',
    nsSeparator: false,
    keySeparator: false,
    logBuild: process.env.APP_ENV !== 'production',
    loader: false,
    skipInitialProps: true,
    loaderName: 'getServerSideProps',
    loadLocaleFrom: async (locale, namespace) => {
        try {
            if (typeof window === 'undefined') {
                const { getRedisClient } = await import('./utils/redis/redisClient');
                const redisClient = await getRedisClient();
                if (redisClient !== undefined) {
                    const redisKey = `${REDIS_PREFIX}${locale}:${namespace}`;

                    const [cachedTranslates, updateJobIsRunning] = await redisClient.mGet([
                        redisKey,
                        `${redisKey}/updating`,
                    ]);

                    if (cachedTranslates === null && updateJobIsRunning === null) {
                        const cacheToRedis = async () => {
                            const setUpdatingFlag = await redisClient.set(`${redisKey}/updating`, 'true', {
                                NX: true,
                                EX: REDIS_UPDATE_JOB_TIMEOUT,
                            });

                            if (setUpdatingFlag !== null) {
                                const getTranslates = (await import('./i18n-translator')).getFreshTranslates;
                                const freshTranslates = await getTranslates(locale, namespace);
                                const translatesToCache = JSON.stringify(freshTranslates);

                                if (translatesToCache) {
                                    await Promise.all([redisClient.set(redisKey, translatesToCache)]);
                                }
                            }
                        };

                        await cacheToRedis().catch(logException);
                    }

                    if (cachedTranslates !== null) {
                        return JSON.parse(cachedTranslates);
                    }
                }
            }
        } catch (error) {
            await logException(error);
        }

        return (await import('./i18n-translator')).getFreshTranslates(locale, namespace);
    },
};
