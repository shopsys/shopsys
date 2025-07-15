import { captureException } from '@sentry/nextjs';
import md5 from 'crypto-js/md5';
import { RedisClientType, RedisFunctions, RedisModules, RedisScripts } from 'redis';
import { isClient } from 'utils/isClient';

const FRIENDLY_URL_REGEXP = `@friendlyUrl` as const;
const CACHE_REGEXP = `@redisCache\\(\\s?ttl:\\s?([0-9]*)\\s?\\)` as const;
const QUERY_NAME_REGEXP = `query\\s([A-z]*)(\\([A-z:!0-9$,\\s]*\\))?\\s@redisCache`;
const getRedisPrefixPattern = () => `${process.env.REDIS_PREFIX}:fe:queryCache:`;

const safelog = (data: any, maxLength = 200) => {
    if (typeof data === 'string') {
        return data.length > maxLength ? data.substring(0, maxLength) + '...[truncated]' : data;
    }
    if (typeof data === 'object' && data !== null) {
        const stringified = JSON.stringify(data);
        return stringified.length > maxLength ? stringified.substring(0, maxLength) + '...[truncated]' : data;
    }
    return data;
};

const removeDirectiveFromQuery = (
    query: string,
    directiveRegexps: (typeof CACHE_REGEXP | typeof FRIENDLY_URL_REGEXP)[],
) => {
    let cleanedQuery = query;
    for (const directiveRegexp of directiveRegexps) {
        cleanedQuery = cleanedQuery.replace(new RegExp(directiveRegexp), '');
    }

    return cleanedQuery;
};

const createInit = (init?: RequestInit | undefined) => ({
    ...init,
    body:
        typeof init?.body === 'string'
            ? removeDirectiveFromQuery(init.body, [CACHE_REGEXP, FRIENDLY_URL_REGEXP])
            : init?.body,
});

export const fetcher =
    (redisClient: RedisClientType<RedisModules, RedisFunctions, RedisScripts> | undefined) =>
    async (input: URL | RequestInfo, init?: RequestInit | undefined): Promise<Response> => {
        console.log('🔍 [GraphQL Redis] Fetcher called with:', {
            input: input.toString(),
            hasRedisClient: !!redisClient,
            isClient,
            graphqlRedisCache: process.env.GRAPHQL_REDIS_CACHE
        });

        if (!isClient && !redisClient) {
            console.warn('⚠️ [GraphQL Redis] Redis client missing on server - cache will not work');
            captureException(
                'Redis client was missing on server. This will cause the Redis cache to not work properly.',
            );
        }

        if (isClient || !init || process.env.GRAPHQL_REDIS_CACHE === '0' || !redisClient) {
            console.log('🔍 [GraphQL Redis] Bypassing cache due to conditions:', {
                isClient,
                hasInit: !!init,
                cacheDisabled: process.env.GRAPHQL_REDIS_CACHE === '0',
                hasRedisClient: !!redisClient
            });
            return fetch(input, createInit(init));
        }

        try {
            console.log('🔍 [GraphQL Redis] Checking for cache directive in body:', safelog(init.body));
            if (typeof init.body !== 'string' || !init.body.match(CACHE_REGEXP)) {
                console.log('🔍 [GraphQL Redis] No cache directive found, proceeding with normal fetch');
                return fetch(input, createInit(init));
            }

            const [, rawTtl] = init.body.match(CACHE_REGEXP) as string[];
            const ttl = parseInt(rawTtl, 10);
            console.log('🔍 [GraphQL Redis] Cache TTL extracted:', { rawTtl, ttl });

            if (ttl <= 0) {
                console.log('🔍 [GraphQL Redis] TTL is 0 or negative, skipping cache');
                return fetch(input, createInit(init));
            }

            const body = removeDirectiveFromQuery(init.body, [CACHE_REGEXP, FRIENDLY_URL_REGEXP]);
            const host = (init.headers ? new Headers(init.headers) : new Headers()).get('OriginalHost');
            const [, queryName] = init.body.match(QUERY_NAME_REGEXP) ?? [];
            const hash = `${getRedisPrefixPattern()}${queryName}:${host}:${md5(body).toString().substring(0, 7)}`;

            console.log('🔍 [GraphQL Redis] Cache key details:', {
                queryName,
                host,
                hash,
                bodyLength: body.length,
                redisPrefix: getRedisPrefixPattern()
            });

            console.log('🔍 [GraphQL Redis] Checking cache for key:', hash);
            const fromCache = await redisClient.get(hash);
            console.log('🔍 [GraphQL Redis] Cache lookup result:', {
                fromCache: fromCache ? 'found' : 'not found',
                cacheDataLength: fromCache?.length,
                cacheDataPreview: safelog(fromCache)
            });

            if (fromCache !== null) {
                console.log('✅ [GraphQL Redis] Returning cached response');
                try {
                    const parsedCache = JSON.parse(fromCache);
                    console.log('🔍 [GraphQL Redis] Parsed cache data:', {
                        parsedCache: safelog(parsedCache),
                        keys: parsedCache ? Object.keys(parsedCache) : 'null/undefined'
                    });

                    const response = new Response(JSON.stringify({ data: parsedCache }), {
                        statusText: 'OK',
                        status: 200,
                        headers: { 'Content-Type': 'application/json' },
                    });
                    return Promise.resolve(response);
                } catch (parseError) {
                    console.error('❌ [GraphQL Redis] Failed to parse cached data:', parseError);
                    console.error('❌ [GraphQL Redis] Raw cached data:', safelog(fromCache));
                    captureException(parseError);
                }
            }

            console.log('🔍 [GraphQL Redis] Cache miss, fetching fresh data');
            const result = await fetch(input, {
                ...init,
                body,
            });

            console.log('🔍 [GraphQL Redis] Fetch result:', {
                status: result.status,
                statusText: result.statusText,
                contentType: result.headers.get('content-type')
            });

            const isJsonContentType = result.headers.get('content-type')?.includes('application/json');

            if (!isJsonContentType) {
                console.warn('⚠️ [GraphQL Redis] Non-JSON response, returning empty object');
                return Promise.resolve(
                    new Response(JSON.stringify({}), {
                        statusText: result.statusText,
                        status: result.status,
                        headers: { 'Content-Type': 'application/json' },
                    }),
                );
            }

            const res = await result.json();
            console.log('🔍 [GraphQL Redis] JSON response:', {
                res: safelog(res),
                hasData: res.data !== undefined,
                hasError: res.error !== undefined,
                dataKeys: res.data ? Object.keys(res.data) : 'null/undefined'
            });

            if (res.data !== undefined && res.error === undefined) {
                console.log('🔍 [GraphQL Redis] Caching successful response with TTL:', ttl);
                await redisClient.set(hash, JSON.stringify(res.data), { EX: ttl });
                console.log('✅ [GraphQL Redis] Response cached successfully');
            } else {
                console.log('🔍 [GraphQL Redis] Not caching due to error or missing data');
            }

            return Promise.resolve(
                new Response(JSON.stringify(res), {
                    statusText: 'OK',
                    status: 200,
                    headers: { 'Content-Type': 'application/json' },
                }),
            );
        } catch (e) {
            console.error('❌ [GraphQL Redis] Error in fetcher:', e);
            captureException(e);

            return fetch(input, createInit(init));
        }
    };
