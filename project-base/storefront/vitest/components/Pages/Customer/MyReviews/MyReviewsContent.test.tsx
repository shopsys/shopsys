import { render } from '@testing-library/react';
import { MyReviewsContent } from 'components/Pages/Customer/MyReviews/MyReviewsContent';
import { TypeCurrentCustomerUserProductReviewsQuery } from 'graphql/requests/productReviews/queries/CurrentCustomerUserProductReviewsQuery.generated';
import { TypeProductReviewStatusEnum } from 'graphql/types';
import { createRef } from 'react';
import { beforeEach, describe, expect, test, vi } from 'vitest';

const testState = vi.hoisted(() => ({
    currentCustomerUserProductReviewsQueryMock: vi.fn(),
    isDesktop: false,
}));

vi.mock('components/Blocks/Pagination/Pagination', () => ({
    Pagination: () => null,
}));

vi.mock('components/Blocks/Skeleton/SkeletonModuleCustomerComplaints', () => ({
    SkeletonModuleCustomerComplaints: () => <div>Loading reviews</div>,
}));

vi.mock('components/Pages/Customer/MyReviews/MyReviewItem', () => ({
    MyReviewItem: ({ productReview }: { productReview: { uuid: string } }) => (
        <li id={`product-review-${productReview.uuid}`} />
    ),
}));

vi.mock('graphql/requests/productReviews/queries/CurrentCustomerUserProductReviewsQuery.generated', () => ({
    useCurrentCustomerUserProductReviewsQuery: testState.currentCustomerUserProductReviewsQueryMock,
}));

vi.mock('graphql/requests/settings/queries/SettingsQuery.generated', () => ({
    useSettingsQuery: () => [{ data: { settings: { productReviewsEnabled: true } } }],
}));

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({ t: (key: string) => key }),
}));

vi.mock('utils/queryParams/useCurrentPageQuery', () => ({
    useCurrentPageQuery: () => 1,
}));

vi.mock('utils/ui/useMediaMin', () => ({
    useMediaMin: () => testState.isDesktop,
}));

const reviewsData: TypeCurrentCustomerUserProductReviewsQuery = {
    currentCustomerUserProductReviews: {
        totalCount: 1,
        pageInfo: {
            __typename: 'PageInfo',
            hasNextPage: false,
            hasPreviousPage: false,
            endCursor: 'review-cursor',
        },
        edges: [
            {
                cursor: 'review-cursor',
                node: {
                    __typename: 'ProductReview',
                    uuid: 'review-uuid',
                    reviewerName: 'John Doe',
                    rating: 5,
                    text: 'Review text',
                    createdAt: '2026-08-27T08:00:00+00:00',
                    isVerifiedPurchase: false,
                    status: TypeProductReviewStatusEnum.Pending,
                    rejectionReason: null,
                    responseText: null,
                    responseCreatedAt: null,
                    rejectedImagesCount: 0,
                    productUuid: 'product-uuid',
                    productName: 'Product',
                    product: null,
                    images: [],
                },
            },
        ],
    },
};

describe('MyReviewsContent', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        testState.isDesktop = false;
        window.history.replaceState({}, '', '#product-review-review-uuid');
    });

    test('scrolls to the linked review after the reviews finish loading', () => {
        const scrollIntoViewMock = vi.fn();
        window.HTMLElement.prototype.scrollIntoView = scrollIntoViewMock;
        vi.spyOn(window, 'requestAnimationFrame').mockImplementation((callback) => {
            callback(0);

            return 1;
        });
        testState.currentCustomerUserProductReviewsQueryMock.mockReturnValue([{ data: undefined, fetching: true }]);
        const paginationScrollTargetRef = createRef<HTMLDivElement>();
        const { rerender } = render(<MyReviewsContent paginationScrollTargetRef={paginationScrollTargetRef} />);

        expect(scrollIntoViewMock).not.toHaveBeenCalled();

        testState.currentCustomerUserProductReviewsQueryMock.mockReturnValue([{ data: reviewsData, fetching: false }]);
        rerender(<MyReviewsContent paginationScrollTargetRef={paginationScrollTargetRef} />);

        expect(scrollIntoViewMock).toHaveBeenCalledOnce();
        expect(scrollIntoViewMock).toHaveBeenCalledWith({ block: 'start' });
    });

    test('scrolls only once when the content remounts for the same history entry', () => {
        const scrollIntoViewMock = vi.fn();
        window.HTMLElement.prototype.scrollIntoView = scrollIntoViewMock;
        vi.spyOn(window, 'requestAnimationFrame').mockImplementation((callback) => {
            callback(0);

            return 1;
        });
        testState.currentCustomerUserProductReviewsQueryMock.mockReturnValue([{ data: reviewsData, fetching: false }]);
        const paginationScrollTargetRef = createRef<HTMLDivElement>();

        const { unmount } = render(<MyReviewsContent paginationScrollTargetRef={paginationScrollTargetRef} />);
        unmount();
        render(<MyReviewsContent paginationScrollTargetRef={paginationScrollTargetRef} />);

        expect(scrollIntoViewMock).toHaveBeenCalledOnce();
    });

    test('leaves hash scroll correction to the common layout on desktop', () => {
        const scrollIntoViewMock = vi.fn();
        window.HTMLElement.prototype.scrollIntoView = scrollIntoViewMock;
        testState.isDesktop = true;
        testState.currentCustomerUserProductReviewsQueryMock.mockReturnValue([{ data: reviewsData, fetching: false }]);

        render(<MyReviewsContent paginationScrollTargetRef={createRef<HTMLDivElement>()} />);

        expect(scrollIntoViewMock).not.toHaveBeenCalled();
    });
});
