import { TypeCartItemFragment } from 'graphql/requests/cart/fragments/CartItemFragment.generated';

export const isCartItemPartiallyAvailable = (
    product: TypeCartItemFragment['product'],
    cartItemQuantity: number,
): boolean =>
    product.isAllowedNegativeStock &&
    product.stockQuantity !== null &&
    product.stockQuantity > 0 &&
    cartItemQuantity > product.stockQuantity;
