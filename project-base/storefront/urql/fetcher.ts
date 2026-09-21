import { captureException } from '@sentry/nextjs';
import type { ServerResponse } from 'http';
import { jwtDecode } from 'jwt-decode';
import { DOMAIN_ID_HEADER } from 'urql/createClient';
import { isClient } from 'utils/isClient';
import type { AppRedisClient } from 'utils/redis/redisClient';
import { recordServerTiming } from 'utils/serverSide/serverTiming';

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

const getAnonymousBucket = async (domainId: string | null): Promise<string> => {
    const defaultPricingGroupId = (domainId && defaultPricingGroupByDomain.get(domainId)) ?? 0;

    const rolesHash = await getHash('');

    return `pg${defaultPricingGroupId}_r${rolesHash}`;
};

const isMalformedAccessToken = (authToken: string): boolean => {
    if (!/^Bearer [A-Za-z0-9_-]+\.[A-Za-z0-9_-]+\.[A-Za-z0-9_-]+$/.test(authToken)) {
        return true;
    }

    try {
        const token = authToken.slice('Bearer '.length);
        const header = jwtDecode<unknown>(token, { header: true });
        const payload = jwtDecode<unknown>(token);

        return [header, payload].some((value) => typeof value !== 'object' || value === null || Array.isArray(value));
    } catch {
        return true;
    }
};

const FRIENDLY_URL_REGEXP = `@friendlyUrl` as const;
const CACHE_REGEXP = `@redisCache(?:PerPricingGroup)?\\(\\s?ttl:\\s?([0-9]*)\\s?\\)` as const;
const PER_PRICING_GROUP_CACHE_REGEXP = `@redisCachePerPricingGroup\\(`;
const QUERY_NAME_REGEXP = `query\\s+([_A-Za-z][_0-9A-Za-z]*)\\b[\\s\\S]*?@redisCache`;
const getRedisPrefixPattern = () => `${process.env.REDIS_PREFIX}:fe:queryCache:`;

const getQueryName = (requestBody: string): string | undefined => {
    try {
        const parsedBody = JSON.parse(requestBody) as { operationName?: unknown };

        if (typeof parsedBody.operationName === 'string' && parsedBody.operationName !== '') {
            return parsedBody.operationName;
        }
    } catch {
        // The fetcher can also receive raw GraphQL bodies, which are handled by the regexp below.
    }

    const [, queryName] = requestBody.match(QUERY_NAME_REGEXP) ?? [];

    return queryName;
};

const hasSetStoreFilterVariables = (requestBody: string): boolean => {
    try {
        const parsedBody = JSON.parse(requestBody) as {
            variables?: {
                after?: unknown;
                coordinates?: unknown;
                searchText?: unknown;
            };
        };

        if (typeof parsedBody.variables !== 'object' || parsedBody.variables === null) {
            return false;
        }

        return (
            parsedBody.variables.searchText != null ||
            parsedBody.variables.coordinates != null ||
            parsedBody.variables.after != null
        );
    } catch {
        return false;
    }
};

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
    (redisClient: AppRedisClient | undefined, response?: ServerResponse) =>
    async (input: URL | RequestInfo, init?: RequestInit | undefined): Promise<Response> => {
        if (isClient || !init || process.env.GRAPHQL_REDIS_CACHE === '0' || !redisClient?.isReady) {
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
            const isPerPricingGroup = init.body.match(PER_PRICING_GROUP_CACHE_REGEXP) !== null;
            const authToken = headers.get('X-Auth-Token');

            // Cache only anonymous pricing responses: the backend must validate each authenticated request,
            // including token expiry and invalidation after customer security changes.
            if (isPerPricingGroup && authToken) {
                // Reject malformed credentials locally while preserving the auth exchange's refresh/cleanup flow.
                // Decodable claims are never trusted to authorize access or select a cache bucket.
                if (isMalformedAccessToken(authToken)) {
                    return Response.json({
                        errors: [{ message: 'Token is not valid.', extensions: { userCode: 'invalid-token' } }],
                    });
                }

                return fetch(createCleanedInput(input), {
                    ...init,
                    body,
                });
            }

            const authBucket = isPerPricingGroup ? `${await getAnonymousBucket(domainId)}:` : '';
            const queryName = getQueryName(init.body);

            if (queryName === undefined || (queryName === 'StoresQuery' && hasSetStoreFilterVariables(init.body))) {
                return fetch(createCleanedInput(input), {
                    ...init,
                    body,
                });
            }

            const key = `${getRedisPrefixPattern()}${queryName}:${host}:${domainId ? `${domainId}:` : ''}${authBucket}`;
            const hash = `${key}${await getHash(body)}`;
            const cacheStartedAt = performance.now();
            const fromCache = await redisClient.get(hash);
            recordServerTiming(
                response,
                `cache_${queryName}`,
                performance.now() - cacheStartedAt,
                fromCache === null ? 'miss' : 'hit',
            );

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
                try {
                    await redisClient.set(hash, JSON.stringify(res.data), { EX: ttl });
                } catch (error) {
                    // A failed cache write must not repeat a successful backend request.
                    captureException(error);
                }

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
