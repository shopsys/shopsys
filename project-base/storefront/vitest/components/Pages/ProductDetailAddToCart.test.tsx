import { render, screen } from '@testing-library/react';
import { ProductDetailAddToCart } from 'components/Pages/ProductDetail/ProductDetailAddToCart/ProductDetailAddToCart';
import { TypeProductDetailFragment } from 'graphql/requests/products/fragments/ProductDetailFragment.generated';
import { TypeAvailabilityStatusEnum } from 'graphql/types';
import { beforeEach, describe, expect, test, vi } from 'vitest';

const { testState } = vi.hoisted(() => ({
    testState: {
        cartItem: undefined as unknown,
        isAddToCartFlowPending: false,
        isAddingToCart: false,
    },
}));

vi.mock('components/Blocks/Product/CartItemQuantityControls', () => ({
    CartItemQuantityControls: () => <div>Quantity controls</div>,
}));

vi.mock('components/providers/AuthorizationProvider', () => ({
    useAuthorization: () => ({ canCreateOrder: true }),
}));

vi.mock('utils/cart/useCurrentCart', () => ({
    useCurrentCart: () => ({ cart: null, isCartFetchingOrUnavailable: false }),
}));

vi.mock('utils/cart/useAddToCartHandler', () => ({
    useAddToCartHandler: () => ({ isAddingToCart: testState.isAddingToCart, onAddToCartHandler: vi.fn() }),
}));

vi.mock('utils/cart/useProductAdditionalServices', () => ({
    useProductAdditionalServices: () => ({
        cartItem: testState.cartItem,
        isAddToCartFlowPending: testState.isAddToCartFlowPending,
        selectedServiceUuids: [],
        updateIsAddToCartFlowPending: vi.fn(),
        onToggleService: vi.fn(),
        persistPendingServicesAfterAddToCart: vi.fn(),
        isSettingAdditionalServices: false,
    }),
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
    beforeEach(() => {
        testState.cartItem = undefined;
        testState.isAddToCartFlowPending = false;
        testState.isAddingToCart = false;
    });

    test('includes product context in the add-to-cart accessible name', () => {
        render(<ProductDetailAddToCart product={product} shouldDisplayAdditionalServices={false} />);

        expect(
            screen.getByRole('button', {
                name: 'Add to cart A4tech mouse X-710BK, quantity 1 piece',
            }),
        ).toBeInTheDocument();
    });

    test('keeps the add-to-cart state visible while selected services are being persisted', () => {
        testState.cartItem = { uuid: 'cart-item-uuid' };
        testState.isAddToCartFlowPending = true;

        render(<ProductDetailAddToCart product={product} shouldDisplayAdditionalServices={false} />);

        expect(screen.getByRole('button', { name: /Add to cart A4tech mouse/ })).toBeInTheDocument();
        expect(screen.queryByText('Quantity controls')).not.toBeInTheDocument();
    });
});
