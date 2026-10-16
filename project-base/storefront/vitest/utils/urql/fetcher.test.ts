import { captureException } from '@sentry/nextjs';
import { RedisClientType } from 'redis';
import { fetcher } from 'urql/fetcher';
import { beforeEach, describe, expect, test, vi } from 'vitest';

const isClientGetter = vi.fn();
vi.mock('utils/isClient', () => ({
    get isClient() {
        return isClientGetter();
    },
}));

vi.mock('@sentry/nextjs', () => ({
    captureException: vi.fn(),
}));

const mockFetch = vi.fn();
global.fetch = mockFetch;

const mockRedisClientGet = vi.fn((): string | null => null);
const mockRedisClientSet = vi.fn(() => null);
const mockRedisClient = {
    get: mockRedisClientGet,
    set: mockRedisClientSet,
} as unknown as RedisClientType;

const REQUEST_WITH_DIRECTIVE = {
    headers: {
        accept: 'application/graphql-response+json, application/graphql+json, application/json, text/event-stream, multipart/mixed',
        originalhost: '127.0.0.1:8000',
        'x-forwarded-proto': 'off',
        'content-type': 'application/json',
    },
    method: 'POST',
    body: '{"operationName":"TestQuery","query":"query TestQuery @redisCache(ttl: 3600) {\\n foobar\\n}"}',
    signal: {},
} as unknown as RequestInit;

const REQUEST_WITHOUT_DIRECTIVE = {
    headers: {
        accept: 'application/graphql-response+json, application/graphql+json, application/json, text/event-stream, multipart/mixed',
        originalhost: '127.0.0.1:8000',
        'x-forwarded-proto': 'off',
        'content-type': 'application/json',
    },
    method: 'POST',
    body: '{"operationName":"TestQuery","query":"query TestQuery  {\\n foobar\\n}"}',
    signal: {},
} as unknown as RequestInit;

const PER_PRICING_GROUP_REQUEST_WITH_FORGED_TOKEN = {
    headers: {
        accept: 'application/graphql-response+json, application/graphql+json, application/json, text/event-stream, multipart/mixed',
        originalhost: '127.0.0.1:8000',
        'x-forwarded-proto': 'off',
        'content-type': 'application/json',
        'x-domain-id': '1',
        'x-auth-token':
            'Bearer eyJhbGciOiJub25lIiwidHlwIjoiSldUIn0.eyJwcmljaW5nR3JvdXBJZCI6NCwicm9sZXMiOlsiUk9MRV9BUElfQUxMIl19.fake',
    },
    method: 'POST',
    body: '{"operationName":"PromotedProductsQuery","query":"query PromotedProductsQuery @redisCachePerPricingGroup(ttl: 3600) {\\n foobar\\n}"}',
    signal: {},
} as unknown as RequestInit;

const STORES_QUERY_REQUEST_WITH_DIRECTIVE = {
    ...REQUEST_WITH_DIRECTIVE,
    body: JSON.stringify({
        operationName: 'StoresQuery',
        variables: {
            searchText: null,
            coordinates: null,
            first: 10,
            after: null,
        },
        query: `query StoresQuery($searchText: String = null, $coordinates: Coordinates = null, $first: Int, $after: String)
@redisCache(ttl: 3600) {
    stores(searchText: $searchText, coordinates: $coordinates, first: $first, after: $after) {
        edges {
            node {
                uuid
            }
        }
    }
}`,
    }),
} as unknown as RequestInit;

const STORES_QUERY_REQUEST_WITH_PARAMETER = {
    ...STORES_QUERY_REQUEST_WITH_DIRECTIVE,
    body: JSON.stringify({
        operationName: 'StoresQuery',
        variables: {
            searchText: 'Praha',
            coordinates: null,
            first: null,
            after: null,
        },
        query: `query StoresQuery($searchText: String = null, $coordinates: Coordinates = null, $first: Int, $after: String)
@redisCache(ttl: 3600) {
    stores(searchText: $searchText, coordinates: $coordinates, first: $first, after: $after) {
        edges {
            node {
                uuid
            }
        }
    }
}`,
    }),
} as unknown as RequestInit;

const TEST_URL = 'https://test.ts/graphql/';
const TEST_URL_WITH_DIRECTIVE =
    'https://test.ts/graphql/?query=query%20TestQuery%20@redisCache(ttl:%203600)%20{%0A%20foobar%0A}';
const TEST_URL_WITH_ENCODED_DIRECTIVE =
    'https://test.ts/graphql/?query=query%20TestQuery%20%40redisCache%28ttl%3A%203600%29%20%7B%0A%20foobar%0A%7D';
const TEST_URL_WITH_FRIENDLY_URL =
    'https://test.ts/graphql/?query=query%20TestQuery%20@friendlyUrl%20{%0A%20foobar%0A}';
const TEST_RESPONSE_BODY = { testBody: 'test data' };

describe('fetcher test', () => {
    beforeEach(() => {
        vi.unstubAllEnvs();
        isClientGetter.mockReset();
        isClientGetter.mockImplementation(() => false);
        mockRedisClientGet.mockReset();
        mockRedisClientSet.mockClear();
        mockRedisClientGet.mockImplementation(() => null);
        mockRedisClientGet.mockImplementation((): string | null => null);
        mockFetch.mockImplementation(() =>
            Promise.resolve({
                headers: new Headers({
                    'content-type': 'application/json',
                }),
                json: () => Promise.resolve({ data: TEST_RESPONSE_BODY }),
            }),
        );
    });

    test('using fetcher on the server without Redis should capture an exception in Sentry but still make a request', () => {
        isClientGetter.mockImplementation(() => false);

        const testFetcher = fetcher(undefined);
        testFetcher(TEST_URL, REQUEST_WITH_DIRECTIVE);

        expect(captureException).toBeCalledWith(
            'Redis client was missing on server. This will cause the Redis cache to not work properly.',
        );
        expect(mockFetch).toBeCalledWith(TEST_URL, REQUEST_WITHOUT_DIRECTIVE);
    });

    test('using fetcher on the client should filter out the cache directive even if used with a Redis client', () => {
        isClientGetter.mockImplementation(() => true);

        const testFetcher = fetcher(mockRedisClient);
        testFetcher(TEST_URL, REQUEST_WITH_DIRECTIVE);

        expect(mockFetch).toBeCalledWith(TEST_URL, REQUEST_WITHOUT_DIRECTIVE);
    });

    test('using fetcher without the Redis cache should filter out the cache directive', () => {
        isClientGetter.mockImplementation(() => false);
        vi.stubEnv('GRAPHQL_REDIS_CACHE', '0');

        const testFetcher = fetcher(mockRedisClient);
        testFetcher(TEST_URL, REQUEST_WITH_DIRECTIVE);

        expect(mockFetch).toBeCalledWith(TEST_URL, REQUEST_WITHOUT_DIRECTIVE);
        vi.unstubAllEnvs();
    });

    test('using fetcher without the Redis client should filter out the cache directive', () => {
        isClientGetter.mockImplementation(() => false);

        const testFetcher = fetcher(undefined);
        testFetcher(TEST_URL, REQUEST_WITH_DIRECTIVE);

        expect(mockFetch).toBeCalledWith(TEST_URL, REQUEST_WITHOUT_DIRECTIVE);
    });

    test('using fetcher on a non-cached query should not call Redis', () => {
        isClientGetter.mockImplementation(() => false);

        const testFetcher = fetcher(mockRedisClient);
        testFetcher(TEST_URL, REQUEST_WITHOUT_DIRECTIVE);

        expect(mockFetch).toBeCalledWith(TEST_URL, REQUEST_WITHOUT_DIRECTIVE);
        expect(mockRedisClient.get).not.toBeCalled();
        expect(mockRedisClient.set).not.toBeCalled();
    });

    test('using fetcher on a not-yet cached query for the first time should set it in Redis', async () => {
        isClientGetter.mockImplementation(() => false);
        vi.stubEnv('REDIS_PREFIX', 'TEST_PREFIX');
        mockFetch.mockImplementation(() =>
            Promise.resolve({
                headers: new Headers({
                    'content-type': 'application/json',
                }),
                json: () => Promise.resolve({ data: TEST_RESPONSE_BODY }),
            }),
        );

        const testFetcher = fetcher(mockRedisClient);
        await testFetcher(TEST_URL, REQUEST_WITH_DIRECTIVE);

        expect(mockFetch).toBeCalledWith(TEST_URL, REQUEST_WITHOUT_DIRECTIVE);
        expect(mockRedisClient.get).toBeCalledWith('TEST_PREFIX:fe:queryCache:TestQuery:127.0.0.1:8000:e0df376');
        expect(mockRedisClient.set).toBeCalledWith(
            'TEST_PREFIX:fe:queryCache:TestQuery:127.0.0.1:8000:e0df376',
            JSON.stringify(TEST_RESPONSE_BODY),
            { EX: 3600 },
        );
    });

    test('using fetcher on StoresQuery should use operation name in Redis cache key', async () => {
        isClientGetter.mockImplementation(() => false);
        vi.stubEnv('REDIS_PREFIX', 'TEST_PREFIX');
        mockFetch.mockImplementation(() =>
            Promise.resolve({
                headers: new Headers({
                    'content-type': 'application/json',
                }),
                json: () => Promise.resolve({ data: TEST_RESPONSE_BODY }),
            }),
        );

        const testFetcher = fetcher(mockRedisClient);
        await testFetcher(TEST_URL, STORES_QUERY_REQUEST_WITH_DIRECTIVE);

        expect(mockRedisClient.get).toBeCalledWith(expect.stringContaining('TEST_PREFIX:fe:queryCache:StoresQuery:'));
        expect(mockRedisClient.get).not.toBeCalledWith(expect.stringContaining('TEST_PREFIX:fe:queryCache:undefined:'));
    });

    test('using fetcher on StoresQuery with a set parameter should not use Redis cache', async () => {
        isClientGetter.mockImplementation(() => false);

        const testFetcher = fetcher(mockRedisClient);
        await testFetcher(TEST_URL, STORES_QUERY_REQUEST_WITH_PARAMETER);

        expect(mockRedisClient.get).not.toBeCalled();
        expect(mockRedisClient.set).not.toBeCalled();
        expect(mockFetch).toBeCalledWith(
            TEST_URL,
            expect.objectContaining({
                body: expect.not.stringContaining('@redisCache'),
            }),
        );
    });

    test('should handle non-JSON content-type responses', async () => {
        isClientGetter.mockImplementation(() => false);
        mockFetch.mockImplementation(() =>
            Promise.resolve({
                headers: new Headers({
                    'content-type': 'text/html',
                }),
            }),
        );

        const testFetcher = fetcher(mockRedisClient);
        const response = await testFetcher(TEST_URL, REQUEST_WITH_DIRECTIVE);
        const responseBody = await response.json();

        expect(responseBody).toStrictEqual({});
        expect(response.headers.get('content-type')).toBe('application/json');
    });

    test('using fetcher on an already cached query should get it from Redis', async () => {
        mockRedisClientGet.mockImplementation(() => JSON.stringify(TEST_RESPONSE_BODY));
        isClientGetter.mockImplementation(() => false);

        const testFetcher = fetcher(mockRedisClient);
        const responseBodyFromRedis = await (await testFetcher(TEST_URL, REQUEST_WITH_DIRECTIVE)).json();

        expect(responseBodyFromRedis).toStrictEqual({ data: TEST_RESPONSE_BODY });
    });

    test.each([
        'Bearer x',
        'Bearer header.payload',
        'Bearer header.payload.signature.extra',
        'Bearer e30.e30.',
        'Bearer invalid.e30.signature',
        'Bearer e30.invalid.signature',
        'Bearer e30.bnVsbA.signature',
        'Bearer e30.W10.signature',
        'Bearer W10.e30.signature',
        'Bearer e30.e30.invalid!signature',
    ])('rejects malformed credentials locally without accessing pricing caches: %s', async (authToken) => {
        const headers = new Headers(PER_PRICING_GROUP_REQUEST_WITH_FORGED_TOKEN.headers);
        headers.set('X-Auth-Token', authToken);
        mockRedisClientGet.mockReturnValue(JSON.stringify({ priceWithVat: '15.000000' }));

        const response = await fetcher(mockRedisClient)(TEST_URL, {
            ...PER_PRICING_GROUP_REQUEST_WITH_FORGED_TOKEN,
            headers,
        });

        expect(mockFetch).not.toHaveBeenCalled();
        expect(response.status).toBe(200);
        expect(response.headers.get('content-type')).toContain('application/json');
        expect(await response.json()).toEqual({
            errors: [{ message: 'Token is not valid.', extensions: { userCode: 'invalid-token' } }],
        });
        expect(mockRedisClient.get).not.toHaveBeenCalled();
        expect(mockRedisClient.set).not.toHaveBeenCalled();
    });

    test.each([
        ['forged JWT', new Headers(PER_PRICING_GROUP_REQUEST_WITH_FORGED_TOKEN.headers).get('x-auth-token')!],
        [
            'forged JWT with future expiry',
            `Bearer eyJhbGciOiJSUzI1NiJ9.${Buffer.from(
                JSON.stringify({ exp: 4102444800, pricingGroupId: 4, roles: ['ROLE_API_ALL'] }),
            ).toString('base64url')}.fake`,
        ],
    ])('returns backend rejection instead of cached VIP data for a %s', async (_scenario, authToken) => {
        const headers = new Headers(PER_PRICING_GROUP_REQUEST_WITH_FORGED_TOKEN.headers);
        headers.set('X-Auth-Token', authToken);
        const request = { ...PER_PRICING_GROUP_REQUEST_WITH_FORGED_TOKEN, headers };
        const backendError = { errors: [{ message: 'Invalid token', extensions: { userCode: 'invalid-token' } }] };
        mockRedisClientGet.mockReturnValue(JSON.stringify({ priceWithVat: '15.000000' }));
        mockFetch.mockResolvedValue(new Response(JSON.stringify(backendError)));

        const testFetcher = fetcher(mockRedisClient);
        const response = await testFetcher(TEST_URL, request);

        expect(await response.json()).toEqual(backendError);
        expect(mockRedisClient.get).not.toBeCalled();
        expect(mockRedisClient.set).not.toBeCalled();
        expect(mockFetch).toHaveBeenCalledExactlyOnceWith(TEST_URL, {
            ...request,
            body: (request.body as string).replace('@redisCachePerPricingGroup(ttl: 3600)', ''),
        });
    });

    test('revalidates an authenticated request after the backend previously accepted the same token', async () => {
        const headers = new Headers(PER_PRICING_GROUP_REQUEST_WITH_FORGED_TOKEN.headers);
        headers.set('X-Auth-Token', 'Bearer eyJhbGciOiJSUzI1NiJ9.eyJleHAiOjQxMDI0NDQ4MDB9.signature');
        const request = { ...PER_PRICING_GROUP_REQUEST_WITH_FORGED_TOKEN, headers };
        const vipData = { data: { priceWithVat: '15.000000' } };
        const backendError = { errors: [{ message: 'Token expired', extensions: { userCode: 'expired-token' } }] };
        mockRedisClientGet.mockReturnValue(JSON.stringify({ priceWithVat: '139.960000' }));
        mockFetch
            .mockResolvedValueOnce(new Response(JSON.stringify(vipData)))
            .mockResolvedValueOnce(new Response(JSON.stringify(backendError)));

        const testFetcher = fetcher(mockRedisClient);
        const acceptedResponse = await testFetcher(TEST_URL, request);
        const expiredResponse = await testFetcher(TEST_URL, request);

        expect(await acceptedResponse.json()).toEqual(vipData);
        expect(await expiredResponse.json()).toEqual(backendError);
        expect(mockFetch).toHaveBeenCalledTimes(2);
        expect(mockRedisClient.get).not.toBeCalled();
        expect(mockRedisClient.set).not.toBeCalled();
    });

    test.each(['1', '2'])('keeps anonymous pricing responses cached on domain %s', async (domainId) => {
        vi.stubEnv('REDIS_PREFIX', 'TEST_PREFIX');
        const headers = new Headers(PER_PRICING_GROUP_REQUEST_WITH_FORGED_TOKEN.headers);
        headers.delete('X-Auth-Token');
        headers.set('X-Domain-Id', domainId);
        const request = { ...PER_PRICING_GROUP_REQUEST_WITH_FORGED_TOKEN, headers };
        mockRedisClientGet.mockReturnValueOnce(null).mockReturnValueOnce(JSON.stringify(TEST_RESPONSE_BODY));

        const testFetcher = fetcher(mockRedisClient);
        const uncachedResponse = await testFetcher(TEST_URL, request);
        const cachedResponse = await testFetcher(TEST_URL, request);

        expect(await uncachedResponse.json()).toEqual({ data: TEST_RESPONSE_BODY });
        expect(await cachedResponse.json()).toEqual({ data: TEST_RESPONSE_BODY });
        expect(mockFetch).toHaveBeenCalledTimes(1);
        expect(mockRedisClient.get).toHaveBeenCalledTimes(2);
        expect(mockRedisClient.set).toHaveBeenCalledExactlyOnceWith(
            expect.stringContaining(`TEST_PREFIX:fe:queryCache:PromotedProductsQuery:127.0.0.1:8000:${domainId}:pg0_`),
            JSON.stringify(TEST_RESPONSE_BODY),
            { EX: 3600 },
        );
    });

    describe('URL directive cleaning', () => {
        test('should clean @redisCache directive from URL string', () => {
            isClientGetter.mockImplementation(() => true);

            const testFetcher = fetcher(mockRedisClient);
            testFetcher(TEST_URL_WITH_DIRECTIVE, REQUEST_WITHOUT_DIRECTIVE);

            const expectedCleanedUrl = 'https://test.ts/graphql/?query=query%20TestQuery%20%20{%0A%20foobar%0A}';
            expect(mockFetch).toBeCalledWith(expectedCleanedUrl, REQUEST_WITHOUT_DIRECTIVE);
        });

        test('should clean encoded @redisCache directive from URL string', () => {
            isClientGetter.mockImplementation(() => true);

            const testFetcher = fetcher(mockRedisClient);
            testFetcher(TEST_URL_WITH_ENCODED_DIRECTIVE, REQUEST_WITHOUT_DIRECTIVE);

            const expectedCleanedUrl = 'https://test.ts/graphql/?query=query%20TestQuery%20%20%7B%0A%20foobar%0A%7D';
            expect(mockFetch).toBeCalledWith(expectedCleanedUrl, REQUEST_WITHOUT_DIRECTIVE);
        });

        test('should clean @friendlyUrl directive from URL string', () => {
            isClientGetter.mockImplementation(() => true);

            const testFetcher = fetcher(mockRedisClient);
            testFetcher(TEST_URL_WITH_FRIENDLY_URL, REQUEST_WITHOUT_DIRECTIVE);

            const expectedCleanedUrl = 'https://test.ts/graphql/?query=query%20TestQuery%20%20{%0A%20foobar%0A}';
            expect(mockFetch).toBeCalledWith(expectedCleanedUrl, REQUEST_WITHOUT_DIRECTIVE);
        });

        test('should clean directives from URL object', () => {
            isClientGetter.mockImplementation(() => true);

            const testFetcher = fetcher(mockRedisClient);
            const urlObject = new URL(TEST_URL_WITH_DIRECTIVE);
            testFetcher(urlObject, REQUEST_WITHOUT_DIRECTIVE);

            const expectedCleanedUrl = new URL(
                'https://test.ts/graphql/?query=query%20TestQuery%20%20{%0A%20foobar%0A}',
            );
            expect(mockFetch).toBeCalledWith(expectedCleanedUrl, REQUEST_WITHOUT_DIRECTIVE);
        });

        test('should handle server-side URL cleaning with Redis cache enabled', async () => {
            isClientGetter.mockImplementation(() => false);
            vi.stubEnv('REDIS_PREFIX', 'TEST_PREFIX');
            mockFetch.mockImplementation(() =>
                Promise.resolve({
                    headers: new Headers({
                        'content-type': 'application/json',
                    }),
                    json: () => Promise.resolve({ data: TEST_RESPONSE_BODY }),
                }),
            );

            const testFetcher = fetcher(mockRedisClient);
            await testFetcher(TEST_URL_WITH_DIRECTIVE, REQUEST_WITH_DIRECTIVE);

            const expectedCleanedUrl = 'https://test.ts/graphql/?query=query%20TestQuery%20%20{%0A%20foobar%0A}';
            expect(mockFetch).toBeCalledWith(expectedCleanedUrl, REQUEST_WITHOUT_DIRECTIVE);
        });

        test('should handle URLs without directives', () => {
            isClientGetter.mockImplementation(() => true);

            const testFetcher = fetcher(mockRedisClient);
            testFetcher(TEST_URL, REQUEST_WITHOUT_DIRECTIVE);

            expect(mockFetch).toBeCalledWith(TEST_URL, REQUEST_WITHOUT_DIRECTIVE);
        });

        test('should clean multiple query parameters correctly', () => {
            isClientGetter.mockImplementation(() => true);

            const urlWithMultipleParams =
                'https://test.ts/graphql/?param1=value1&query=test@redisCache(ttl:3600)&param2=value2';
            const expectedCleanedUrl = 'https://test.ts/graphql/?param1=value1&query=test&param2=value2';

            const testFetcher = fetcher(mockRedisClient);
            testFetcher(urlWithMultipleParams, REQUEST_WITHOUT_DIRECTIVE);

            expect(mockFetch).toBeCalledWith(expectedCleanedUrl, REQUEST_WITHOUT_DIRECTIVE);
        });

        test('should handle error scenarios with URL cleaning', async () => {
            isClientGetter.mockImplementation(() => false);
            mockFetch.mockImplementation(() => Promise.reject(new Error('Network error')));

            const testFetcher = fetcher(mockRedisClient);

            await expect(testFetcher(TEST_URL_WITH_DIRECTIVE, REQUEST_WITH_DIRECTIVE)).rejects.toThrow('Network error');

            expect(captureException).toBeCalledWith(new Error('Network error'));
            const expectedCleanedUrl = 'https://test.ts/graphql/?query=query%20TestQuery%20%20{%0A%20foobar%0A}';
            expect(mockFetch).toBeCalledWith(expectedCleanedUrl, REQUEST_WITHOUT_DIRECTIVE);
        });
    });
});
