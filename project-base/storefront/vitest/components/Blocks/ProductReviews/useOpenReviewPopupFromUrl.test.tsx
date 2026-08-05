import { renderHook, waitFor } from '@testing-library/react';
import { useOpenReviewPopupFromUrl } from 'components/Blocks/ProductReviews/useOpenReviewPopupFromUrl';
import { beforeEach, describe, expect, test, vi } from 'vitest';

const testState = vi.hoisted(() => ({
    isReviewAvailabilityLoading: false,
    openCreateProductReviewPopupMock: vi.fn(),
    pushQueriesMock: vi.fn(),
    reviewedProductUuids: new Set<string>(),
    router: {
        query: {
            productSlug: 'product-slug',
            writeReviewOrderHash: 'order-url-hash',
            writeReviewProduct: 'product-uuid',
        },
    },
}));

vi.mock('next/router', () => ({
    useRouter: () => testState.router,
}));

vi.mock('components/Blocks/ProductReviews/useCurrentCustomerUserProductFamilyReviews', () => ({
    useCurrentCustomerUserProductFamilyReviews: () => ({
        isLoading: testState.isReviewAvailabilityLoading,
        pendingOwnReviews: [],
        reviewedProductUuids: testState.reviewedProductUuids,
    }),
}));

vi.mock('components/Blocks/ProductReviews/useOpenCreateProductReviewPopup', () => ({
    useOpenCreateProductReviewPopup: () => testState.openCreateProductReviewPopupMock,
}));

vi.mock('graphql/requests/productReviews/queries/ProductReviewOrderPrefillQuery.generated', () => ({
    useProductReviewOrderPrefillQuery: () => [{ data: undefined, fetching: false }],
}));

vi.mock('graphql/requests/settings/queries/SettingsQuery.generated', () => ({
    useSettingsQuery: () => [{ data: { settings: { productReviewsEnabled: true } } }],
}));

vi.mock('utils/auth/useIsUserLoggedIn', () => ({
    useIsUserLoggedIn: () => true,
}));

vi.mock('utils/queryParams/pushQueries', () => ({
    pushQueries: testState.pushQueriesMock,
}));

describe('useOpenReviewPopupFromUrl', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        testState.isReviewAvailabilityLoading = false;
        testState.reviewedProductUuids = new Set();
        testState.router.query.writeReviewProduct = 'product-uuid';
        testState.openCreateProductReviewPopupMock.mockResolvedValue(undefined);
    });

    test('does not open the popup when the customer already reviewed the requested product', async () => {
        testState.reviewedProductUuids = new Set(['product-uuid']);

        renderHook(() => useOpenReviewPopupFromUrl('product-uuid', 'Product name'));

        await waitFor(() => expect(testState.pushQueriesMock).toHaveBeenCalledOnce());
        expect(testState.openCreateProductReviewPopupMock).not.toHaveBeenCalled();
    });

    test('opens the popup when the customer has not reviewed the requested product', async () => {
        renderHook(() => useOpenReviewPopupFromUrl('product-uuid', 'Product name'));

        await waitFor(() =>
            expect(testState.openCreateProductReviewPopupMock).toHaveBeenCalledWith({
                guestPrefill: undefined,
                orderUrlHash: 'order-url-hash',
                productName: 'Product name',
                productUuid: 'product-uuid',
                variants: undefined,
            }),
        );
    });

    test('does not open the popup for a product that is not shown on the current page', async () => {
        testState.router.query.writeReviewProduct = 'foreign-product-uuid';

        renderHook(() => useOpenReviewPopupFromUrl('product-uuid', 'Product name'));

        await waitFor(() => expect(testState.pushQueriesMock).toHaveBeenCalledOnce());
        expect(testState.openCreateProductReviewPopupMock).not.toHaveBeenCalled();
    });

    test('opens the popup for a variant shown on the current page', async () => {
        const variants = [{ uuid: 'variant-uuid', fullName: 'Variant name' }];
        testState.router.query.writeReviewProduct = 'variant-uuid';

        renderHook(() => useOpenReviewPopupFromUrl('product-uuid', 'Product name', variants));

        await waitFor(() =>
            expect(testState.openCreateProductReviewPopupMock).toHaveBeenCalledWith({
                guestPrefill: undefined,
                orderUrlHash: 'order-url-hash',
                productName: 'Product name',
                productUuid: 'variant-uuid',
                variants,
            }),
        );
    });
});
