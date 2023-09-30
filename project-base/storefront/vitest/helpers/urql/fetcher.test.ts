import { RedisClientType } from 'redis';
import { fetcher } from 'urql/fetcher';
import { Mock, describe, expect, test, vi } from 'vitest';
import { isServer } from 'helpers/isServer';
import { captureException } from '@sentry/nextjs';

vi.mock('helpers/isServer', () => ({
    isServer: vi.fn(),
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
const TEST_RESPONSE_BODY = { testBody: 'test data' };

describe('fetcher test', () => {
    test('using fetcher on the server without Redis should capture an exception in Sentry but still make a request', () => {
        (isServer as Mock).mockImplementation(() => true);

        const testFetcher = fetcher(undefined);
        testFetcher(TEST_URL, REQUEST_WITH_DIRECTIVE);

        expect(captureException).toBeCalledWith(
            'Redis client was missing on server. This will cause the Redis cache to not work properly.',
        );
        expect(mockFetch).toBeCalledWith(TEST_URL, REQUEST_WITHOUT_DIRECTIVE);
    });

    test('using fetcher on the client should filter out the cache directive even if used with a Redis client', () => {
        (isServer as Mock).mockImplementation(() => false);

        const testFetcher = fetcher(mockRedisClient);
        testFetcher(TEST_URL, REQUEST_WITH_DIRECTIVE);

        expect(mockFetch).toBeCalledWith(TEST_URL, REQUEST_WITHOUT_DIRECTIVE);
    });

    test('using fetcher without the Redis cache should filter out the cache directive', () => {
        (isServer as Mock).mockImplementation(() => true);
        vi.stubEnv('GRAPHQL_REDIS_CACHE', '0');

        const testFetcher = fetcher(mockRedisClient);
        testFetcher(TEST_URL, REQUEST_WITH_DIRECTIVE);

        expect(mockFetch).toBeCalledWith(TEST_URL, REQUEST_WITHOUT_DIRECTIVE);
        vi.unstubAllEnvs();
    });

    test('using fetcher without the Redis client should filter out the cache directive', () => {
        (isServer as Mock).mockImplementation(() => true);

        const testFetcher = fetcher(undefined);
        testFetcher(TEST_URL, REQUEST_WITH_DIRECTIVE);

        expect(mockFetch).toBeCalledWith(TEST_URL, REQUEST_WITHOUT_DIRECTIVE);
    });

    test('using fetcher on a non-cached query should not call Redis', () => {
        (isServer as Mock).mockImplementation(() => true);

        const testFetcher = fetcher(mockRedisClient);
        testFetcher(TEST_URL, REQUEST_WITHOUT_DIRECTIVE);

        expect(mockFetch).toBeCalledWith(TEST_URL, REQUEST_WITHOUT_DIRECTIVE);
        expect(mockRedisClient.get).not.toBeCalled();
        expect(mockRedisClient.set).not.toBeCalled();
    });

    test('using fetcher on a not-yet cached query for the first time should set it in Redis', async () => {
        (isServer as Mock).mockImplementation(() => true);
        vi.stubEnv('REDIS_PREFIX', 'TEST_PREFIX');
        mockFetch.mockImplementation(() =>
            Promise.resolve({
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

    test('using fetcher on an already cached query should get it from Redis', async () => {
        mockRedisClientGet.mockImplementation(() => JSON.stringify(TEST_RESPONSE_BODY));
        (isServer as Mock).mockImplementation(() => true);

        const testFetcher = fetcher(mockRedisClient);
        const responseBodyFromRedis = await (await testFetcher(TEST_URL, REQUEST_WITH_DIRECTIVE)).json();

        expect(responseBodyFromRedis).toStrictEqual({ data: TEST_RESPONSE_BODY });
    });
});
