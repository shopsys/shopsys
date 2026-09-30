import { STRUCTURED_DATA_REVIEWS_COUNT } from 'components/Basic/Head/productMetadataConstants';
import { ProductReviewsQueryDocument } from 'graphql/requests/productReviews/queries/ProductReviewsQuery.generated';
import { ProductDetailQueryDocument } from 'graphql/requests/products/queries/ProductDetailQuery.generated';
import { TypeProductReviewOrderingModeEnum } from 'graphql/types';
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

const createPendingQuery = () => {
    let resolve!: () => void;
    const promise = new Promise<void>((finish) => {
        resolve = finish;
    });
    return { promise, resolve };
};

describe('product detail SSR prefetch', () => {
    const query = vi.fn();
    let reviews: ReturnType<typeof createPendingQuery>;
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
        reviews = createPendingQuery();
        vi.mocked(createClient).mockReturnValue({ query } as unknown as ReturnType<typeof createClient>);
        vi.mocked(prefetchLayoutQueries).mockResolvedValue({ resolvedQueries: [], seoPageSlug: null });
        vi.mocked(buildServerSideProps).mockResolvedValue({ props: { prefetched: true } } as any);
        query.mockImplementation((document) => {
            if (document === ProductDetailQueryDocument) {
                return { toPromise: () => Promise.resolve({ data: { product } }) };
            }
            if (document === ProductReviewsQueryDocument) {
                return { toPromise: () => reviews.promise };
            }
            throw new Error('Unexpected query');
        });
    });

    test.each([
        { type: 'RegularProduct' as const, isLuigisBoxActive: true },
        { type: 'RegularProduct' as const, isLuigisBoxActive: false },
        { type: 'MainVariant' as const, isLuigisBoxActive: true },
    ])('waits for structured-data reviews without fetching recommendations for %j', async ({
        type,
        isLuigisBoxActive,
    }) => {
        product.__typename = type;
        const result = runSsr(isLuigisBoxActive);

        await vi.waitFor(() => {
            expect(query).toHaveBeenCalledWith(ProductReviewsQueryDocument, {
                productUuid: 'test-product',
                orderingMode: TypeProductReviewOrderingModeEnum.Newest,
                first: STRUCTURED_DATA_REVIEWS_COUNT,
                after: null,
            });
        });
        expect(buildServerSideProps).not.toHaveBeenCalled();

        reviews.resolve();
        expect(await result).toEqual({ props: { prefetched: true } });
        expect(buildServerSideProps).toHaveBeenCalledTimes(1);
        expect(query.mock.calls.map(([document]) => document)).toEqual([
            ProductDetailQueryDocument,
            ProductReviewsQueryDocument,
        ]);
    });

    test.each([
        null,
        { totalCount: 0 },
    ])('returns without fetching reviews or recommendations for summary %j', async (summary) => {
        product.reviewsSummary = summary;

        const result = await runSsr();

        expect(result).toEqual({ props: { prefetched: true } });
        expect(query.mock.calls.map(([document]) => document)).toEqual([ProductDetailQueryDocument]);
    });
});
