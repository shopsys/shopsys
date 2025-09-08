import { captureException } from '@sentry/nextjs';
import md5 from 'crypto-js/md5';
import { RedisClientType, RedisFunctions, RedisModules, RedisScripts } from 'redis';
import { isClient } from 'utils/isClient';

const FRIENDLY_URL_REGEXP = `@friendlyUrl` as const;
const CACHE_REGEXP = `@redisCache\\(\\s?ttl:\\s?([0-9]*)\\s?\\)` as const;
const QUERY_NAME_REGEXP = `query\\s([A-z]*)(\\([A-z:!0-9$,\\s]*\\))?\\s@redisCache`;
const getRedisPrefixPattern = () => `${process.env.REDIS_PREFIX}:fe:queryCache:`;

// For URL-encoded: %40redisCache%28ttl%3A%203600%29 -> %40redisCache followed by optional %28...%29
// For unencoded: @redisCache(ttl: 3600) -> @redisCache followed by optional (...)
const URL_CACHE_REGEXP = /%40redisCache%28.*?%29|@redisCache\([^)]*\)|%40redisCache|@redisCache/g;
const URL_FRIENDLY_URL_REGEXP = /%40friendlyUrl|@friendlyUrl/g;

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

const createCleanedInput = (input: URL | RequestInfo): URL | RequestInfo => {
    if (typeof input === 'string') {
        if (
            input.includes('@redisCache') ||
            input.includes('%40redisCache') ||
            input.includes('@friendlyUrl') ||
            input.includes('%40friendlyUrl')
        ) {
            let cleanedUrl = input.replace(URL_CACHE_REGEXP, '').replace(URL_FRIENDLY_URL_REGEXP, '');

            cleanedUrl = cleanedUrl
                .replace(/[?&]{2,}/g, '?') // Replace multiple ? or & with single ?
                .replace(/[?&]$/, '') // Remove trailing ? or &
                .replace(/&{2,}/g, '&'); // Replace multiple & with single &

            return cleanedUrl;
        }
    } else if (input instanceof URL) {
        const urlString = input.toString();

        if (
            urlString.includes('@redisCache') ||
            urlString.includes('%40redisCache') ||
            urlString.includes('@friendlyUrl') ||
            urlString.includes('%40friendlyUrl')
        ) {
            const cleanedUrlString = createCleanedInput(urlString) as string;
            return new URL(cleanedUrlString);
        }
    }

    return input;
};

export const fetcher =
    (redisClient: RedisClientType<RedisModules, RedisFunctions, RedisScripts> | undefined) =>
    async (input: URL | RequestInfo, init?: RequestInit | undefined): Promise<Response> => {
        if (!isClient && !redisClient) {
            captureException(
                'Redis client was missing on server. This will cause the Redis cache to not work properly.',
            );
        }

        if (isClient || !init || process.env.GRAPHQL_REDIS_CACHE === '0' || !redisClient) {
            return fetch(createCleanedInput(input), createInit(init));
        }

        try {
            if (typeof init.body !== 'string' || !init.body.match(CACHE_REGEXP)) {
                return fetch(createCleanedInput(input), createInit(init));
            }

            const [, rawTtl] = init.body.match(CACHE_REGEXP) as string[];
            const ttl = parseInt(rawTtl, 10);

            if (ttl <= 0) {
                return fetch(createCleanedInput(input), createInit(init));
            }

            const body = removeDirectiveFromQuery(init.body, [CACHE_REGEXP, FRIENDLY_URL_REGEXP]);
            const host = (init.headers ? new Headers(init.headers) : new Headers()).get('OriginalHost');
            const [, queryName] = init.body.match(QUERY_NAME_REGEXP) ?? [];
            const hash = `${getRedisPrefixPattern()}${queryName}:${host}:${md5(body).toString().substring(0, 7)}`;

            const fromCache = await redisClient.get(hash);

            if (fromCache !== null) {
                const response = new Response(JSON.stringify({ data: JSON.parse(fromCache) }), {
                    statusText: 'OK',
                    status: 200,
                    headers: { 'Content-Type': 'application/json' },
                });
                return Promise.resolve(response);
            }

            const result = await fetch(createCleanedInput(input), {
                ...init,
                body,
            });

            const isJsonContentType = result.headers.get('content-type')?.includes('application/json');

            if (!isJsonContentType) {
                return Promise.resolve(
                    new Response(JSON.stringify({}), {
                        statusText: result.statusText,
                        status: result.status,
                        headers: { 'Content-Type': 'application/json' },
                    }),
                );
            }

            const res = await result.json();

            if (res.data !== undefined && res.error === undefined) {
                await redisClient.set(hash, JSON.stringify(res.data), { EX: ttl });
            }

            return Promise.resolve(
                new Response(JSON.stringify(res), {
                    statusText: 'OK',
                    status: 200,
                    headers: { 'Content-Type': 'application/json' },
                }),
            );
        } catch (e) {
            captureException(e);

            return fetch(createCleanedInput(input), createInit(init));
        }
    };
