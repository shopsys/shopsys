import { render, screen } from '@testing-library/react';
import { ProductDetailAddToCart } from 'components/Pages/ProductDetail/ProductDetailAddToCart/ProductDetailAddToCart';
import { TypeProductDetailFragment } from 'graphql/requests/products/fragments/ProductDetailFragment.generated';
import { TypeAvailabilityStatusEnum } from 'graphql/types';
import { describe, expect, test, vi } from 'vitest';

vi.mock('components/providers/AuthorizationProvider', () => ({
    useAuthorization: () => ({ canCreateOrder: true }),
}));

vi.mock('utils/cart/useCurrentCart', () => ({
    useCurrentCart: () => ({ cart: null, isCartFetchingOrUnavailable: false }),
}));

vi.mock('utils/cart/useAddToCartHandler', () => ({
    useAddToCartHandler: () => ({ isAddingToCart: false, onAddToCartHandler: vi.fn() }),
}));

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({
        t: (key: string, options?: Record<string, string | number>) =>
            Object.entries(options ?? {}).reduce(
                (translatedKey, [optionKey, optionValue]) =>
                    translatedKey.replaceAll(`{{ ${optionKey} }}`, String(optionValue)),
                key,
            ),
    }),
}));

const product = {
    availability: { status: TypeAvailabilityStatusEnum.InStock },
    fullName: 'A4tech mouse X-710BK',
    isCurrentlyOutOfStock: false,
    isInquiryType: false,
    isSellingDenied: false,
    unit: { name: 'piece' },
    uuid: 'product-uuid',
} as TypeProductDetailFragment;

describe('ProductDetailAddToCart', () => {
    test('includes product context in the add-to-cart accessible name', () => {
        render(<ProductDetailAddToCart product={product} />);

        expect(
            screen.getByRole('button', {
                name: 'Add to cart A4tech mouse X-710BK, quantity 1 piece',
            }),
        ).toBeInTheDocument();
    });
});
