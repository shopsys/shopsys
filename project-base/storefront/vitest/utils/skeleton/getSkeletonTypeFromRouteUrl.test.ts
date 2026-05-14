import { getSkeletonTypeFromRouteUrl } from 'utils/skeleton/getSkeletonTypeFromRouteUrl';
import { describe, expect, test } from 'vitest';

describe('getSkeletonTypeFromRouteUrl test', () => {
    test('returns skeleton type from friendly URL slug type query parameter', () => {
        expect(getSkeletonTypeFromRouteUrl('/products/[productSlug]?slugType=front_product_detail')).toBe('product');
        expect(getSkeletonTypeFromRouteUrl('/categories/[categorySlug]?slugType=front_product_list')).toBe('category');
    });

    test('returns skeleton type from internal friendly route', () => {
        expect(getSkeletonTypeFromRouteUrl('/products/test-product')).toBe('product');
        expect(getSkeletonTypeFromRouteUrl('/categories/test-category')).toBe('category');
        expect(getSkeletonTypeFromRouteUrl('/catalog')).toBe('catalog');
    });

    test('returns undefined for unknown route', () => {
        expect(getSkeletonTypeFromRouteUrl('/unknown-route')).toBeUndefined();
        expect(getSkeletonTypeFromRouteUrl('/cart')).toBeUndefined();
        expect(getSkeletonTypeFromRouteUrl('/kosik')).toBeUndefined();
    });

    test('returns homepage skeleton type from root route', () => {
        expect(getSkeletonTypeFromRouteUrl('/')).toBe('homepage');
    });
});
