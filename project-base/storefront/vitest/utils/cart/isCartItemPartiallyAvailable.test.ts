import { TypeCartItemFragment } from 'graphql/requests/cart/fragments/CartItemFragment.generated';
import { isCartItemPartiallyAvailable } from 'utils/cart/isCartItemPartiallyAvailable';
import { describe, expect, it } from 'vitest';

const createProduct = (stockQuantity: number | null, isAllowedNegativeStock: boolean) =>
    ({ stockQuantity, isAllowedNegativeStock }) as TypeCartItemFragment['product'];

describe('isCartItemPartiallyAvailable', () => {
    it('returns true when the cart quantity exceeds a non-zero stock and negative stock is allowed', () => {
        expect(isCartItemPartiallyAvailable(createProduct(2, true), 5)).toBe(true);
    });

    it('returns false when the whole cart quantity is covered by the stock', () => {
        expect(isCartItemPartiallyAvailable(createProduct(5, true), 5)).toBe(false);
    });

    it('returns false when the product is completely out of stock', () => {
        expect(isCartItemPartiallyAvailable(createProduct(0, true), 5)).toBe(false);
    });

    it('returns false when the stock quantity is unknown', () => {
        expect(isCartItemPartiallyAvailable(createProduct(null, true), 5)).toBe(false);
    });

    it('returns false when negative stock is not allowed', () => {
        expect(isCartItemPartiallyAvailable(createProduct(2, false), 5)).toBe(false);
    });
});
