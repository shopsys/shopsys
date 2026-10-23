// @vitest-environment node
import { fetcher } from 'urql/fetcher';
import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest';

vi.mock('@sentry/nextjs', () => ({ captureException: vi.fn() }));
vi.mock('utils/isClient', () => ({ isClient: false }));
vi.mock('urql/createClient', () => ({ DOMAIN_ID_HEADER: 'X-Domain-Id' }));

const versionKey = 'test:fe:queryCacheVersion:PromotedProductsQuery';
const createRequest = (queryName = 'PromotedProductsQuery') => ({
    method: 'POST',
    headers: { OriginalHost: 'example.com', 'X-Domain-Id': '1' },
    body: JSON.stringify({
        query: `query ${queryName} @redisCachePerPricingGroup(ttl: 3600) { promotedProducts { id } }`,
    }),
});
const createResponse = (id: number) =>
    new Response(JSON.stringify({ data: { promotedProducts: [{ id }] } }), {
        headers: { 'Content-Type': 'application/json' },
    });

describe('promoted products cache invalidation', () => {
    beforeEach(() => {
        vi.stubEnv('REDIS_PREFIX', 'test');
        vi.stubEnv('GRAPHQL_REDIS_CACHE', '1');
    });

    afterEach(() => {
        vi.unstubAllEnvs();
        vi.unstubAllGlobals();
    });

    test.each([
        null,
        '7',
    ])('ignores a stale response written after invalidation from version %s and caches the fresh response', async (initialVersion) => {
        const cache = new Map<string, string>();
        if (initialVersion !== null) {
            cache.set(versionKey, initialVersion);
        }
        const redis = {
            get: vi.fn(async (key: string) => cache.get(key) ?? null),
            set: vi.fn(async (key: string, value: string) => cache.set(key, value)),
        };
        let finishOldRequest!: (response: Response) => void;
        let signalRequestStarted!: () => void;
        const requestStarted = new Promise<void>((resolve) => {
            signalRequestStarted = resolve;
        });
        const fetchMock = vi.fn().mockImplementationOnce(
            () =>
                new Promise<Response>((resolve) => {
                    finishOldRequest = resolve;
                    signalRequestStarted();
                }),
        );
        fetchMock.mockResolvedValueOnce(createResponse(49));
        vi.stubGlobal('fetch', fetchMock);
        const fetchFromCache = fetcher(redis as unknown as NonNullable<Parameters<typeof fetcher>[0]>);

        const oldRequest = fetchFromCache('https://example.com/graphql', createRequest());
        await requestStarted;
        cache.clear();
        cache.set(versionKey, String(Number(initialVersion) + 1));
        finishOldRequest(createResponse(33));
        await oldRequest;

        const freshResponse = await fetchFromCache('https://example.com/graphql', createRequest());
        expect(await freshResponse.json()).toEqual({ data: { promotedProducts: [{ id: 49 }] } });
        const cachedResponse = await fetchFromCache('https://example.com/graphql', createRequest());
        expect(await cachedResponse.json()).toEqual({ data: { promotedProducts: [{ id: 49 }] } });
        expect(fetchMock).toHaveBeenCalledTimes(2);
        expect(redis.set).toHaveBeenLastCalledWith(expect.any(String), expect.any(String), { EX: 3600 });
    });

    test('preserves caching of other queries when promoted products are invalidated', async () => {
        const cache = new Map<string, string>();
        const redis = {
            get: vi.fn(async (key: string) => cache.get(key) ?? null),
            set: vi.fn(async (key: string, value: string) => cache.set(key, value)),
        };
        const fetchMock = vi.fn().mockResolvedValue(createResponse(49));
        vi.stubGlobal('fetch', fetchMock);
        const fetchFromCache = fetcher(redis as unknown as NonNullable<Parameters<typeof fetcher>[0]>);
        await fetchFromCache('https://example.com/graphql', createRequest('NavigationQuery'));

        cache.set(versionKey, '1');

        const cachedResponse = await fetchFromCache('https://example.com/graphql', createRequest('NavigationQuery'));
        expect(await cachedResponse.json()).toEqual({ data: { promotedProducts: [{ id: 49 }] } });
        expect(fetchMock).toHaveBeenCalledTimes(1);
    });
});
