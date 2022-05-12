// eslint-disable-next-line @typescript-eslint/no-var-requires
const Sentry = require('@sentry/nextjs');

const REDIS_URL = `redis://${process.env.REDIS_HOST}`;
const REDIS_PREFIX = `${process.env.REDIS_PREFIX}:fe:translates:`;
const REDIS_UPDATE_JOB_TIMEOUT = 5; // seconds (default: 30)
const REDIS_IS_CACHED_TIMEOUT = 30; // seconds (default: 5 * 60)

module.exports = {
    pages: {
        '*': ['common'],
    },
    locales: ['cs', 'sk', 'en'],
    defaultLocale: 'cs',
    localeDetection: false,
    serializeConfig: false,
    defaultNS: 'common',
    keySeparator: false,
    nsSeparator: false,
    loader: false,
    skipInitialProps: true,
    loaderName: 'getServerSideProps',
    interpolation: {
        format: (value, format, lng) => {
            if (format === 'formatPrice') {
                return Intl.NumberFormat(lng, {
                    style: 'currency',
                    currency: value.currencyCode,
                }).format(value.price);
            }
            return value;
        },
    },
    loadLocaleFrom: async (locale, namespace) => {
        try {
            if (typeof window === 'undefined') {
                const redis = await import('redis');
                const redisKey = `${REDIS_PREFIX}${locale}:${namespace}`;

                const redisClient = redis.createClient({
                    url: REDIS_URL,
                    socket: {
                        connectTimeout: 5000,
                        reconnectStrategy: () => new Error('The Redis server refused the connection.'),
                    },
                });

                await redisClient.connect();

                const [cachedTranslates, isCached, updateJobIsRunning] = await redisClient.mGet([
                    redisKey,
                    redisKey + '/cached',
                    redisKey + '/updating',
                ]);

                if (isCached === null && updateJobIsRunning === null) {
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
                                Promise.all([
                                    redisClient.set(redisKey, translatesToCache),
                                    redisClient.set(redisKey + '/cached', 'true', {
                                        EX: REDIS_IS_CACHED_TIMEOUT,
                                    }),
                                ]);
                            }
                        }
                    };

                    cacheToRedis().catch((reject) => {
                        Sentry.captureException(reject);
                    });
                }

                if (cachedTranslates !== null) {
                    return JSON.parse(cachedTranslates);
                }
            }
        } catch (error) {
            Sentry.captureException(error);
        }

        return (await import('./i18n-translator')).getLocalTranslates(locale, namespace);
    },
};
