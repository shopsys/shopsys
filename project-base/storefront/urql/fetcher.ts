import { captureException } from '@sentry/nextjs';
import { RedisClientType, RedisFunctions, RedisModules, RedisScripts } from 'redis';
import { CURRENCY_CODE_HEADER, DOMAIN_ID_HEADER } from 'urql/createClient';
import { isClient } from 'utils/isClient';

// Server-side only hash function for Redis cache keys
const getHash = async (data: string): Promise<string> => {
    const crypto = await import('crypto');
    return crypto.createHash('md5').update(data).digest('hex').substring(0, 7);
};

const defaultPricingGroupByDomain = new Map<string, number>();

const observeSettingsResponseForDefaultPricingGroup = (domainId: string | null, data: unknown): void => {
    if (!domainId || typeof data !== 'object' || data === null) {
        return;
    }

    const id = (data as { settings?: { defaultPricingGroupId?: unknown } }).settings?.defaultPricingGroupId;

    if (typeof id === 'number') {
        defaultPricingGroupByDomain.set(domainId, id);
    }
};

const getAuthBucketFromHeaders = async (headers: Headers, domainId: string | null): Promise<string> => {
    const authToken = headers.get('X-Auth-Token')?.replace(/^Bearer\s+/i, '');
    const defaultPricingGroupId = (domainId && defaultPricingGroupByDomain.get(domainId)) ?? 0;
    let pricingGroupId: number | string = defaultPricingGroupId;
    let roles: string[] = [];

    if (authToken) {
        try {
            const [, payloadSegment] = authToken.split('.');
            const payload = JSON.parse(Buffer.from(payloadSegment, 'base64url').toString('utf8'));
            pricingGroupId = payload?.pricingGroupId ?? defaultPricingGroupId;
            roles = payload?.roles ?? [];
        } catch {
            // Malformed token — keep defaults.
        }
    }

    const rolesHash = await getHash([...roles].sort().join(','));

    return `pg${pricingGroupId}_r${rolesHash}`;
};

const FRIENDLY_URL_REGEXP = `@friendlyUrl` as const;
const CACHE_REGEXP = `@redisCache(?:PerPricingGroup)?\\(\\s?ttl:\\s?([0-9]*)\\s?\\)` as const;
const PER_PRICING_GROUP_CACHE_REGEXP = `@redisCachePerPricingGroup\\(`;
const QUERY_NAME_REGEXP = `query\\s([A-z]*)(\\([A-z:!0-9$,\\s]*\\))?\\s@redisCache`;
const getRedisPrefixPattern = () => `${process.env.REDIS_PREFIX}:fe:queryCache:`;

// For URL-encoded: %40redisCache%28ttl%3A%203600%29 -> %40redisCache followed by optional %28...%29
// For unencoded: @redisCache(ttl: 3600) -> @redisCache followed by optional (...)
// Order matters: the variants that consume `(...)` must come before the bare ones so
// `@redisCachePerPricingGroup(...)` is stripped whole instead of leaving `(ttl: ...)` behind.
const URL_CACHE_REGEXP =
    /%40redisCache(?:PerPricingGroup)?%28.*?%29|@redisCache(?:PerPricingGroup)?\([^)]*\)|%40redisCache(?:PerPricingGroup)?|@redisCache(?:PerPricingGroup)?/g;
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
            const headers = init.headers ? new Headers(init.headers) : new Headers();
            const host = headers.get('OriginalHost');
            const domainId = headers.get(DOMAIN_ID_HEADER);
            const currencyCode = headers.get(CURRENCY_CODE_HEADER);
            const currencyBucket = currencyCode ? `${currencyCode}:` : '';
            const isPerPricingGroup = init.body.match(PER_PRICING_GROUP_CACHE_REGEXP) !== null;
            const authBucket = isPerPricingGroup ? `${await getAuthBucketFromHeaders(headers, domainId)}:` : '';
            const [, queryName] = init.body.match(QUERY_NAME_REGEXP) ?? [];
            const key = `${getRedisPrefixPattern()}${queryName}:${host}:${domainId ? `${domainId}:` : ''}${currencyBucket}${authBucket}`;
            const hash = `${key}${await getHash(body)}`;
            const fromCache = await redisClient.get(hash);

            if (fromCache !== null) {
                const data = JSON.parse(fromCache);

                if (queryName === 'SettingsQuery') {
                    observeSettingsResponseForDefaultPricingGroup(domainId, data);
                }

                const response = new Response(JSON.stringify({ data }), {
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

                if (queryName === 'SettingsQuery') {
                    observeSettingsResponseForDefaultPricingGroup(domainId, res.data);
                }
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
