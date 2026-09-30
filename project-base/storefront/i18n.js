const REDIS_PREFIX = `${process.env.REDIS_PREFIX}:fe:translates:`;
const REDIS_UPDATE_JOB_TIMEOUT = 5; // seconds (default: 30)

const logException = async (e) => {
    if (process.env.APP_ENV === 'development') {
        // biome-ignore lint/suspicious/noConsole: intentional error logging in development
        console.error(e);
    }

    let parsedException;

    try {
        if (e instanceof Error) {
            parsedException = { message: e.message, cause: e.cause, name: e.name, stack: e.stack };
        } else {
            parsedException = JSON.stringify(e);
        }
    } catch {
        parsedException = 'Unknown exception thrown inside i18n.js loadLocaleFrom function';
    }

    fetch(`${process.env.INTERNAL_ENDPOINT}api/log-exception`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ exception: parsedException }),
    });
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

                        await cacheToRedis().catch((reject) => {
                            logException(reject);
                        });
                    }

                    if (cachedTranslates !== null) {
                        return JSON.parse(cachedTranslates);
                    }
                }
            }
        } catch (error) {
            logException(error);
        }

        return (await import('./i18n-translator')).getFreshTranslates(locale, namespace);
    },
};
