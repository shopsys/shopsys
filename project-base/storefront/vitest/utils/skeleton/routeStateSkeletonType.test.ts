import { getSkeletonTypeFromRouteState } from 'utils/skeleton/routeStateSkeletonType';
import { describe, expect, test } from 'vitest';

describe('getSkeletonTypeFromRouteState test', () => {
    test('returns skeleton type stored in route state', () => {
        expect(getSkeletonTypeFromRouteState({ shopsysSkeletonType: 'cart', url: '/unknown-route' })).toBe('cart');
    });

    test('falls back to skeleton type from route url', () => {
        expect(getSkeletonTypeFromRouteState({ url: '/products/[productSlug]?slugType=front_product_detail' })).toBe(
            'product',
        );
    });

    test('falls back to skeleton type from displayed route url', () => {
        expect(getSkeletonTypeFromRouteState({ as: '/' })).toBe('homepage');
    });
});
