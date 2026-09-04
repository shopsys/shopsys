import { renderHook } from '@testing-library/react';
import {
    CURRENT_CUSTOMER_USER_REVIEWS_LIMIT,
    useCurrentCustomerUserReviewedProductUuids,
} from 'components/Blocks/ProductReviews/useCurrentCustomerUserReviewedProductUuids';
import { beforeEach, describe, expect, test, vi } from 'vitest';

const testState = vi.hoisted(() => ({
    currentCustomerUserReviewedProductUuidsQueryMock: vi.fn(),
}));

vi.mock('graphql/requests/customer/queries/CurrentCustomerUserQuery.generated', () => ({
    useCurrentCustomerUserQuery: () => [{ data: { currentCustomerUser: {} }, fetching: false }],
}));

vi.mock('graphql/requests/productReviews/queries/CurrentCustomerUserReviewedProductUuidsQuery.generated', () => ({
    useCurrentCustomerUserReviewedProductUuidsQuery: testState.currentCustomerUserReviewedProductUuidsQueryMock,
}));

vi.mock('graphql/requests/settings/queries/SettingsQuery.generated', () => ({
    useSettingsQuery: () => [{ data: { settings: { productReviewsEnabled: true } } }],
}));

describe('useCurrentCustomerUserReviewedProductUuids', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        testState.currentCustomerUserReviewedProductUuidsQueryMock.mockReturnValue([
            {
                data: {
                    currentCustomerUserProductReviews: {
                        edges: [{ node: { productUuid: 'reviewed-product-uuid' } }],
                    },
                },
                fetching: false,
            },
        ]);
    });

    test('returns reviewed product uuids using a query within the GraphQL complexity limit', () => {
        const { result } = renderHook(() => useCurrentCustomerUserReviewedProductUuids());

        expect(result.current.reviewedProductUuids).toEqual(new Set(['reviewed-product-uuid']));
        expect(testState.currentCustomerUserReviewedProductUuidsQueryMock).toHaveBeenCalledWith({
            variables: { first: CURRENT_CUSTOMER_USER_REVIEWS_LIMIT },
            pause: false,
            requestPolicy: 'cache-and-network',
        });
        expect(CURRENT_CUSTOMER_USER_REVIEWS_LIMIT).toBe(50);
    });
});
