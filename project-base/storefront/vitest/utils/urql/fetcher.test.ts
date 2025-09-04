import { captureException } from '@sentry/nextjs';
import { RedisClientType } from 'redis';
import { fetcher } from 'urql/fetcher';
import { Mock, describe, expect, test, vi } from 'vitest';

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

const mockRedisClientGet: Mock<[], null | string> = vi.fn(() => null);
const mockRedisClient = {
    get: mockRedisClientGet,
    set: vi.fn(() => null),
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

const TEST_URL = 'https://test.ts/graphql/';
const TEST_URL_WITH_DIRECTIVE =
    'https://test.ts/graphql/?query=query%20TestQuery%20@redisCache(ttl:%203600)%20{%0A%20foobar%0A}';
const TEST_URL_WITH_ENCODED_DIRECTIVE =
    'https://test.ts/graphql/?query=query%20TestQuery%20%40redisCache%28ttl%3A%203600%29%20%7B%0A%20foobar%0A%7D';
const TEST_URL_WITH_FRIENDLY_URL =
    'https://test.ts/graphql/?query=query%20TestQuery%20@friendlyUrl%20{%0A%20foobar%0A}';
const TEST_RESPONSE_BODY = { testBody: 'test data' };

describe('fetcher test', () => {
    test('using fetcher on the server without Redis should capture an exception in Sentry but still make a request', () => {
        (isClientGetter as Mock).mockImplementation(() => false);

        const testFetcher = fetcher(undefined);
        testFetcher(TEST_URL, REQUEST_WITH_DIRECTIVE);

        expect(captureException).toBeCalledWith(
            'Redis client was missing on server. This will cause the Redis cache to not work properly.',
        );
        expect(mockFetch).toBeCalledWith(TEST_URL, REQUEST_WITHOUT_DIRECTIVE);
    });

    test('using fetcher on the client should filter out the cache directive even if used with a Redis client', () => {
        (isClientGetter as Mock).mockImplementation(() => true);

        const testFetcher = fetcher(mockRedisClient);
        testFetcher(TEST_URL, REQUEST_WITH_DIRECTIVE);

        expect(mockFetch).toBeCalledWith(TEST_URL, REQUEST_WITHOUT_DIRECTIVE);
    });

    test('using fetcher without the Redis cache should filter out the cache directive', () => {
        (isClientGetter as Mock).mockImplementation(() => false);
        vi.stubEnv('GRAPHQL_REDIS_CACHE', '0');

        const testFetcher = fetcher(mockRedisClient);
        testFetcher(TEST_URL, REQUEST_WITH_DIRECTIVE);

        expect(mockFetch).toBeCalledWith(TEST_URL, REQUEST_WITHOUT_DIRECTIVE);
        vi.unstubAllEnvs();
    });

    test('using fetcher without the Redis client should filter out the cache directive', () => {
        (isClientGetter as Mock).mockImplementation(() => false);

        const testFetcher = fetcher(undefined);
        testFetcher(TEST_URL, REQUEST_WITH_DIRECTIVE);

        expect(mockFetch).toBeCalledWith(TEST_URL, REQUEST_WITHOUT_DIRECTIVE);
    });

    test('using fetcher on a non-cached query should not call Redis', () => {
        (isClientGetter as Mock).mockImplementation(() => false);

        const testFetcher = fetcher(mockRedisClient);
        testFetcher(TEST_URL, REQUEST_WITHOUT_DIRECTIVE);

        expect(mockFetch).toBeCalledWith(TEST_URL, REQUEST_WITHOUT_DIRECTIVE);
        expect(mockRedisClient.get).not.toBeCalled();
        expect(mockRedisClient.set).not.toBeCalled();
    });

    test('using fetcher on a not-yet cached query for the first time should set it in Redis', async () => {
        (isClientGetter as Mock).mockImplementation(() => false);
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
        vi.unstubAllEnvs();
    });

    test('should handle non-JSON content-type responses', async () => {
        (isClientGetter as Mock).mockImplementation(() => false);
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
        (isClientGetter as Mock).mockImplementation(() => false);

        const testFetcher = fetcher(mockRedisClient);
        const responseBodyFromRedis = await (await testFetcher(TEST_URL, REQUEST_WITH_DIRECTIVE)).json();

        expect(responseBodyFromRedis).toStrictEqual({ data: TEST_RESPONSE_BODY });
    });

    describe('URL directive cleaning', () => {
        test('should clean @redisCache directive from URL string', () => {
            (isClientGetter as Mock).mockImplementation(() => true);

            const testFetcher = fetcher(mockRedisClient);
            testFetcher(TEST_URL_WITH_DIRECTIVE, REQUEST_WITHOUT_DIRECTIVE);

            const expectedCleanedUrl = 'https://test.ts/graphql/?query=query%20TestQuery%20%20{%0A%20foobar%0A}';
            expect(mockFetch).toBeCalledWith(expectedCleanedUrl, REQUEST_WITHOUT_DIRECTIVE);
        });

        test('should clean encoded @redisCache directive from URL string', () => {
            (isClientGetter as Mock).mockImplementation(() => true);

            const testFetcher = fetcher(mockRedisClient);
            testFetcher(TEST_URL_WITH_ENCODED_DIRECTIVE, REQUEST_WITHOUT_DIRECTIVE);

            const expectedCleanedUrl = 'https://test.ts/graphql/?query=query%20TestQuery%20%20%7B%0A%20foobar%0A%7D';
            expect(mockFetch).toBeCalledWith(expectedCleanedUrl, REQUEST_WITHOUT_DIRECTIVE);
        });

        test('should clean @friendlyUrl directive from URL string', () => {
            (isClientGetter as Mock).mockImplementation(() => true);

            const testFetcher = fetcher(mockRedisClient);
            testFetcher(TEST_URL_WITH_FRIENDLY_URL, REQUEST_WITHOUT_DIRECTIVE);

            const expectedCleanedUrl = 'https://test.ts/graphql/?query=query%20TestQuery%20%20{%0A%20foobar%0A}';
            expect(mockFetch).toBeCalledWith(expectedCleanedUrl, REQUEST_WITHOUT_DIRECTIVE);
        });

        test('should clean directives from URL object', () => {
            (isClientGetter as Mock).mockImplementation(() => true);

            const testFetcher = fetcher(mockRedisClient);
            const urlObject = new URL(TEST_URL_WITH_DIRECTIVE);
            testFetcher(urlObject, REQUEST_WITHOUT_DIRECTIVE);

            const expectedCleanedUrl = new URL(
                'https://test.ts/graphql/?query=query%20TestQuery%20%20{%0A%20foobar%0A}',
            );
            expect(mockFetch).toBeCalledWith(expectedCleanedUrl, REQUEST_WITHOUT_DIRECTIVE);
        });

        test('should handle server-side URL cleaning with Redis cache enabled', async () => {
            (isClientGetter as Mock).mockImplementation(() => false);
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
            vi.unstubAllEnvs();
        });

        test('should handle URLs without directives', () => {
            (isClientGetter as Mock).mockImplementation(() => true);

            const testFetcher = fetcher(mockRedisClient);
            testFetcher(TEST_URL, REQUEST_WITHOUT_DIRECTIVE);

            expect(mockFetch).toBeCalledWith(TEST_URL, REQUEST_WITHOUT_DIRECTIVE);
        });

        test('should clean multiple query parameters correctly', () => {
            (isClientGetter as Mock).mockImplementation(() => true);

            const urlWithMultipleParams =
                'https://test.ts/graphql/?param1=value1&query=test@redisCache(ttl:3600)&param2=value2';
            const expectedCleanedUrl = 'https://test.ts/graphql/?param1=value1&query=test&param2=value2';

            const testFetcher = fetcher(mockRedisClient);
            testFetcher(urlWithMultipleParams, REQUEST_WITHOUT_DIRECTIVE);

            expect(mockFetch).toBeCalledWith(expectedCleanedUrl, REQUEST_WITHOUT_DIRECTIVE);
        });

        test('should handle error scenarios with URL cleaning', async () => {
            (isClientGetter as Mock).mockImplementation(() => false);
            mockFetch.mockImplementation(() => Promise.reject(new Error('Network error')));

            const testFetcher = fetcher(mockRedisClient);

            await expect(testFetcher(TEST_URL_WITH_DIRECTIVE, REQUEST_WITH_DIRECTIVE)).rejects.toThrow('Network error');

            expect(captureException).toBeCalledWith(new Error('Network error'));
            const expectedCleanedUrl = 'https://test.ts/graphql/?query=query%20TestQuery%20%20{%0A%20foobar%0A}';
            expect(mockFetch).toBeCalledWith(expectedCleanedUrl, REQUEST_WITHOUT_DIRECTIVE);
        });
    });
});
