import { Cache } from '@urql/exchange-graphcache';
import { cacheUpdates } from 'urql/cache/updates';
import { describe, expect, test, vi } from 'vitest';

describe('cacheUpdates', () => {
    test('refreshes remaining complaint quantities in order details and ordered item lists', () => {
        const fields = ['complaints', 'order', 'orderItems', 'orderItemsSearch', 'cart'];
        const invalidateMock = vi.fn();
        const cache = {
            inspectFields: vi.fn().mockReturnValue(fields.map((fieldName) => ({ fieldKey: fieldName, fieldName }))),
            invalidate: invalidateMock,
        } as unknown as Cache;
        const createComplaintUpdater = cacheUpdates.Mutation?.CreateComplaint;

        if (createComplaintUpdater === undefined) {
            throw new Error('CreateComplaint cache updater is not configured.');
        }

        createComplaintUpdater({} as never, {} as never, cache, {} as never);

        expect(invalidateMock).toHaveBeenCalledTimes(4);
        expect(invalidateMock).toHaveBeenCalledWith('Query', 'complaints');
        expect(invalidateMock).toHaveBeenCalledWith('Query', 'order');
        expect(invalidateMock).toHaveBeenCalledWith('Query', 'orderItems');
        expect(invalidateMock).toHaveBeenCalledWith('Query', 'orderItemsSearch');
        expect(invalidateMock).not.toHaveBeenCalledWith('Query', 'cart');
    });

    test('invalidates order data after creating a product review', () => {
        const invalidateMock = vi.fn();
        const cache = {
            inspectFields: vi.fn().mockReturnValue([
                { fieldKey: 'currentCustomerUserProductReviews', fieldName: 'currentCustomerUserProductReviews' },
                { fieldKey: 'order({"urlHash":"order-url-hash"})', fieldName: 'order' },
                { fieldKey: 'cart', fieldName: 'cart' },
            ]),
            invalidate: invalidateMock,
        } as unknown as Cache;
        const createProductReviewUpdater = cacheUpdates.Mutation?.CreateProductReview;

        if (createProductReviewUpdater === undefined) {
            throw new Error('CreateProductReview cache updater is not configured.');
        }

        createProductReviewUpdater({} as never, {} as never, cache, {} as never);

        expect(invalidateMock).toHaveBeenCalledTimes(2);
        expect(invalidateMock).toHaveBeenNthCalledWith(1, 'Query', 'currentCustomerUserProductReviews');
        expect(invalidateMock).toHaveBeenNthCalledWith(2, 'Query', 'order({"urlHash":"order-url-hash"})');
    });
});
