const REDIS_URL = `redis://${process.env.REDIS_HOST}`;
const REDIS_PREFIX = `${process.env.REDIS_PREFIX}:fe:translates:`;
const REDIS_UPDATE_JOB_TIMEOUT = 5; // seconds (default: 30)

const logException = async (e) => {
    if (process.env.APP_ENV === 'development') {
        // eslint-disable-next-line no-console
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

    fetch(process.env.INTERNAL_ENDPOINT + '/api/log-exception', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ exception: parsedException }),
    });
};

module.exports = {
    pages: {
        '*': ['common'],
    },
    locales: ['en', 'cs'],
    defaultLocale: 'en',
    localeDetection: false,
    serializeConfig: false,
    defaultNS: 'common',
    keySeparator: false,
    logBuild: process.env.APP_ENV !== 'production',
    nsSeparator: false,
    loader: false,
    skipInitialProps: true,
    loaderName: 'getServerSideProps',
    loadLocaleFrom: async (locale, namespace) => {
        let redisClient;
        try {
            if (typeof window === 'undefined') {
                const redis = await import('redis');
                const redisKey = `${REDIS_PREFIX}${locale}:${namespace}`;

                redisClient = redis.createClient({
                    url: REDIS_URL,
                    socket: {
                        connectTimeout: 5000,
                        reconnectStrategy: () => new Error('The Redis server refused the connection.'),
                    },
                });

                await redisClient.connect();

                const [cachedTranslates, updateJobIsRunning] = await redisClient.mGet([
                    redisKey,
                    redisKey + '/updating',
                ]);

                if (cachedTranslates === null && updateJobIsRunning === null) {
                    const cacheToRedis = async () => {
                        const setUpdatingFlag = await redisClient.set(redisKey + '/updating', 'true', {
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

                await redisClient.disconnect();
                if (cachedTranslates !== null) {
                    return JSON.parse(cachedTranslates);
                }
            }
        } catch (error) {
            logException(error);
        } finally {
            if (redisClient?.isOpen) {
                await redisClient.disconnect();
            }
        }

        return (await import('./i18n-translator')).getFreshTranslates(locale, namespace);
    },
};
