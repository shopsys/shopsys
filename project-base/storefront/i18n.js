const REDIS_URL = `redis://${process.env.REDIS_HOST}`;
const REDIS_PREFIX = `${process.env.REDIS_PREFIX}:fe:translates:`;
const REDIS_UPDATE_JOB_TIMEOUT = 5; // seconds (default: 30)

const safelog = (data, maxLength = 200) => {
    if (typeof data === 'string') {
        return data.length > maxLength ? data.substring(0, maxLength) + '...[truncated]' : data;
    }
    if (typeof data === 'object' && data !== null) {
        const stringified = JSON.stringify(data);
        return stringified.length > maxLength ? stringified.substring(0, maxLength) + '...[truncated]' : data;
    }
    return data;
};

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

    fetch(process.env.INTERNAL_ENDPOINT + 'api/log-exception', {
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
        console.log('🔍 [Redis] loadLocaleFrom called with:', { locale, namespace });
        console.log('🔍 [Redis] Environment variables:', { 
            REDIS_HOST: process.env.REDIS_HOST, 
            REDIS_PREFIX: process.env.REDIS_PREFIX,
            REDIS_URL: REDIS_URL 
        });

        try {
            if (typeof window === 'undefined') {
                const redis = await import('redis');
                const redisKey = `${REDIS_PREFIX}${locale}:${namespace}`;
                console.log('🔍 [Redis] Generated redis key:', redisKey);

                redisClient = redis.createClient({
                    url: REDIS_URL,
                    socket: {
                        connectTimeout: 5000,
                        reconnectStrategy: () => new Error('The Redis server refused the connection.'),
                    },
                });

                console.log('🔍 [Redis] Attempting to connect to Redis...');
                await redisClient.connect();
                console.log('✅ [Redis] Successfully connected to Redis');

                console.log('🔍 [Redis] Performing mGet operation for keys:', [redisKey, redisKey + '/updating']);
                const [cachedTranslates, updateJobIsRunning] = await redisClient.mGet([
                    redisKey,
                    redisKey + '/updating',
                ]);

                console.log('🔍 [Redis] mGet results:', { 
                    cachedTranslates: safelog(cachedTranslates), 
                    cachedTranslatesType: typeof cachedTranslates,
                    cachedTranslatesLength: cachedTranslates?.length,
                    updateJobIsRunning: updateJobIsRunning 
                });

                if (cachedTranslates === null && updateJobIsRunning === null) {
                    console.log('🔍 [Redis] No cached data and no update job running, starting cache update process');
                    const cacheToRedis = async () => {
                        console.log('🔍 [Redis] Setting updating flag...');
                        const setUpdatingFlag = await redisClient.set(redisKey + '/updating', 'true', {
                            NX: true,
                            EX: REDIS_UPDATE_JOB_TIMEOUT,
                        });

                        console.log('🔍 [Redis] Update flag set result:', setUpdatingFlag);
                        if (setUpdatingFlag !== null) {
                            console.log('🔍 [Redis] Fetching fresh translations...');
                            const getTranslates = (await import('./i18n-translator')).getFreshTranslates;
                            const freshTranslates = await getTranslates(locale, namespace);
                            console.log('🔍 [Redis] Fresh translations received:', { 
                                freshTranslates: safelog(freshTranslates), 
                                type: typeof freshTranslates,
                                keys: freshTranslates ? Object.keys(freshTranslates) : 'null/undefined' 
                            });
                            
                            const translatesToCache = JSON.stringify(freshTranslates);
                            console.log('🔍 [Redis] Serialized translations:', { 
                                translatesToCache: safelog(translatesToCache), 
                                length: translatesToCache?.length 
                            });

                            if (translatesToCache) {
                                console.log('🔍 [Redis] Storing translations in cache...');
                                await Promise.all([redisClient.set(redisKey, translatesToCache)]);
                                console.log('✅ [Redis] Translations stored in cache');
                            } else {
                                console.warn('⚠️ [Redis] No translations to cache - translatesToCache is empty');
                            }
                        }
                    };

                    await cacheToRedis().catch((reject) => {
                        console.error('❌ [Redis] Cache update failed:', reject);
                        logException(reject);
                    });
                }

                console.log('🔍 [Redis] Disconnecting from Redis...');
                await redisClient.disconnect();
                console.log('✅ [Redis] Disconnected from Redis');
                
                if (cachedTranslates !== null) {
                    console.log('🔍 [Redis] Parsing cached translations...');
                    try {
                        const parsedTranslates = JSON.parse(cachedTranslates);
                        console.log('✅ [Redis] Successfully parsed cached translations:', { 
                            parsedTranslates: safelog(parsedTranslates), 
                            keys: parsedTranslates ? Object.keys(parsedTranslates) : 'null/undefined' 
                        });
                        return parsedTranslates;
                    } catch (parseError) {
                        console.error('❌ [Redis] Failed to parse cached translations:', parseError);
                        console.error('❌ [Redis] Raw cached data:', safelog(cachedTranslates));
                        logException(parseError);
                    }
                } else {
                    console.log('🔍 [Redis] No cached translations found, will fetch fresh translations');
                }
            }
        } catch (error) {
            console.error('❌ [Redis] Error in loadLocaleFrom:', error);
            logException(error);
        } finally {
            if (redisClient?.isOpen) {
                console.log('🔍 [Redis] Closing Redis connection in finally block...');
                await redisClient.disconnect();
                console.log('✅ [Redis] Redis connection closed in finally block');
            }
        }

        console.log('🔍 [Redis] Falling back to fresh translations...');
        const fallbackTranslates = await (await import('./i18n-translator')).getFreshTranslates(locale, namespace);
        console.log('🔍 [Redis] Fallback translations received:', { 
            fallbackTranslates: safelog(fallbackTranslates), 
            keys: fallbackTranslates ? Object.keys(fallbackTranslates) : 'null/undefined' 
        });
        return fallbackTranslates;
    },
};
