import { ProductDetailQueryDocument } from 'graphql/requests/products/queries/ProductDetailQuery.generated';
import { getServerSideProps } from 'pages/products/[productSlug]';
import { createClient } from 'urql/createClient';
import { buildServerSideProps, prefetchLayoutQueries } from 'utils/serverSide/initServerSideProps';
import { beforeEach, describe, expect, test, vi } from 'vitest';
import { defaultTestDomainConfig } from 'vitest/helpers/mockPublicConfig';

vi.mock('utils/serverSide/getServerSidePropsWrapper', () => ({
    getServerSidePropsWrapper: (callback: unknown) => callback,
}));
vi.mock('urql/createClient', () => ({ createClient: vi.fn() }));
vi.mock('utils/serverSide/initServerSideProps', () => ({
    prefetchLayoutQueries: vi.fn(),
    buildServerSideProps: vi.fn(),
}));
vi.mock('utils/errors/handleServerSideErrorResponseForFriendlyUrls', () => ({
    handleServerSideErrorResponseForFriendlyUrls: () => undefined,
}));

describe('product detail SSR prefetch', () => {
    const query = vi.fn();
    let product: {
        __typename: 'RegularProduct' | 'MainVariant';
        uuid: string;
        reviewsSummary: { totalCount: number } | null;
    };

    const runSsr = (isLuigisBoxActive = true) =>
        getServerSideProps({
            redisClient: undefined,
            domainConfig: { ...defaultTestDomainConfig, isLuigisBoxActive },
            ssrExchange: {},
            t: (key: string) => key,
        })({
            req: { url: '/test-product', headers: {} },
            resolvedUrl: '/test-product',
        });

    beforeEach(() => {
        product = { __typename: 'RegularProduct', uuid: 'test-product', reviewsSummary: { totalCount: 2 } };
        vi.mocked(createClient).mockReturnValue({ query } as unknown as ReturnType<typeof createClient>);
        vi.mocked(prefetchLayoutQueries).mockResolvedValue({ resolvedQueries: [], seoPageSlug: null });
        vi.mocked(buildServerSideProps).mockResolvedValue({ props: { prefetched: true } } as any);
        query.mockImplementation((document) => {
            if (document === ProductDetailQueryDocument) {
                return { toPromise: () => Promise.resolve({ data: { product } }) };
            }
            throw new Error('Unexpected query');
        });
    });

    test.each([
        { type: 'RegularProduct' as const, isLuigisBoxActive: true },
        { type: 'RegularProduct' as const, isLuigisBoxActive: false },
        { type: 'MainVariant' as const, isLuigisBoxActive: true },
    ])(
        'uses product data without separate review or recommendation queries for %j',
        async ({ type, isLuigisBoxActive }) => {
            product.__typename = type;
            const result = await runSsr(isLuigisBoxActive);

            expect(result).toEqual({ props: { prefetched: true } });
            expect(buildServerSideProps).toHaveBeenCalledWith(
                expect.objectContaining({ pageQueryResults: [{ data: { product } }] }),
            );
            expect(query.mock.calls.map(([document]) => document)).toEqual([ProductDetailQueryDocument]);
        },
    );

    test.each([null, { totalCount: 0 }])(
        'returns without fetching reviews or recommendations for summary %j',
        async (summary) => {
            product.reviewsSummary = summary;

            const result = await runSsr();

            expect(result).toEqual({ props: { prefetched: true } });
            expect(query.mock.calls.map(([document]) => document)).toEqual([ProductDetailQueryDocument]);
        },
    );
});
