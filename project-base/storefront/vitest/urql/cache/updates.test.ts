import { Cache } from '@urql/exchange-graphcache';
import { cacheUpdates } from 'urql/cache/updates';
import { describe, expect, test, vi } from 'vitest';

describe('cacheUpdates', () => {
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

    test.each([
        ['an anonymous', 'cart-1'],
        ['a logged-in customer', null],
    ])('writes the cart of a refused order into the cart query of %s cart', (_, cartUuid) => {
        const updateQueryMock = vi.fn();
        const cache = { inspectFields: vi.fn().mockReturnValue([]), invalidate: vi.fn(), updateQuery: updateQueryMock };
        const createOrderUpdater = cacheUpdates.Mutation?.CreateOrder;

        if (createOrderUpdater === undefined) {
            throw new Error('CreateOrder cache updater is not configured.');
        }

        const refusedOrder = { CreateOrder: { orderCreated: false, order: null, cart: { uuid: cartUuid } } };
        createOrderUpdater(refusedOrder as never, {} as never, cache as unknown as Cache, {} as never);

        expect(updateQueryMock).toHaveBeenCalledOnce();
        expect(updateQueryMock.mock.calls[0][0].variables).toEqual({ cartUuid });
        expect(updateQueryMock.mock.calls[0][1]().cart).toBe(refusedOrder.CreateOrder.cart);
    });
});
