import { act, renderHook } from '@testing-library/react';
import { useProductReviewsData } from 'components/Blocks/ProductReviews/useProductReviewsData';
import { TypeProductReviewsQuery } from 'graphql/requests/productReviews/queries/ProductReviewsQuery.generated';
import { TypeProductReviewOrderingModeEnum } from 'graphql/types';
import { beforeEach, describe, expect, test, vi } from 'vitest';

const testState = vi.hoisted(() => ({
    clientQueryMock: vi.fn(),
    queryData: undefined as TypeProductReviewsQuery | undefined,
}));

vi.mock('urql', () => ({
    useClient: () => ({ query: testState.clientQueryMock }),
    useQuery: () => [{ data: testState.queryData, fetching: false }],
}));

const createReviewEdge = (index: number) => ({
    cursor: `cursor-${index}`,
    node: {
        __typename: 'ProductReview' as const,
        uuid: `review-${index}`,
        productName: 'Product',
        reviewerName: `Reviewer ${index}`,
        rating: 5,
        text: `Review ${index}`,
        createdAt: '2026-08-25T08:00:00+00:00',
        isVerifiedPurchase: false,
        responseText: null,
        responseCreatedAt: null,
        images: [],
    },
});

const createProductReviews = (
    reviewIndexes: number[],
    totalCount: number,
): TypeProductReviewsQuery['productReviews'] => ({
    totalCount,
    orderingMode: TypeProductReviewOrderingModeEnum.Newest,
    summary: {
        __typename: 'ProductReviewsSummary',
        averageRating: 5,
        totalCount,
        ratingCounts: [],
    },
    pageInfo: {
        __typename: 'PageInfo',
        hasNextPage: false,
        hasPreviousPage: false,
        endCursor: `cursor-${reviewIndexes.at(-1)}`,
    },
    edges: reviewIndexes.map(createReviewEdge),
});

describe('useProductReviewsData', () => {
    beforeEach(() => {
        testState.queryData = {
            productReviews: createProductReviews([1, 2, 3, 4, 5], 6),
        };
        testState.clientQueryMock.mockReset();
        testState.clientQueryMock.mockReturnValue({
            toPromise: vi.fn().mockResolvedValue({
                data: {
                    productReviews: createProductReviews([6], 6),
                },
            }),
        });
    });

    test('loads remaining reviews based on their total count', async () => {
        const { result } = renderHook(() => useProductReviewsData('product-uuid'));

        await act(async () => result.current.loadMoreReviews());

        expect(result.current.reviews).toHaveLength(6);
        expect(result.current.hasMoreReviews).toBe(false);
    });
});
