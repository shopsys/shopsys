import { render, screen } from '@testing-library/react';
import type { ProductListItemLayoutProps } from 'components/Blocks/Product/ProductsList/ProductListItemActions';
import { ProductsListContent } from 'components/Blocks/Product/ProductsList/ProductsListContent';
import type { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import type { CurrentCartType } from 'types/cart';
import { beforeEach, describe, expect, test, vi } from 'vitest';

const state = vi.hoisted(() => ({
    cart: { cart: undefined, isCartFetchingOrUnavailable: true } as Pick<
        CurrentCartType,
        'cart' | 'isCartFetchingOrUnavailable'
    >,
    canCreateOrder: true,
    useCurrentCart: vi.fn(),
}));

vi.mock('utils/cart/useCurrentCart', () => ({
    useCurrentCart: () => {
        state.useCurrentCart();
        return state.cart;
    },
}));
vi.mock('components/providers/AuthorizationProvider', () => ({
    useAuthorization: () => ({ canCreateOrder: state.canCreateOrder, canSeePrices: true }),
}));
vi.mock('components/providers/DomainConfigProvider', () => ({
    useDomainConfig: () => ({ url: 'https://example.com' }),
}));
vi.mock('utils/queryParams/useCurrentPageQuery', () => ({ useCurrentPageQuery: () => 1 }));
vi.mock('utils/productLists/comparison/useComparison', () => ({
    useComparison: () => ({ toggleProductInComparison: vi.fn(), isProductInComparison: () => false }),
}));
vi.mock('utils/productLists/wishlist/useWishlist', () => ({
    useWishlist: () => ({ toggleProductInWishlist: vi.fn(), isProductInWishlist: () => false }),
}));
vi.mock('gtm/handlers/onGtmProductClickEventHandler', () => ({ onGtmProductClickEventHandler: vi.fn() }));

// Keep the list and card logic real; expose the state handed to each visual layout.
const LayoutProbe = ({ currentCart, shouldShowProductActionSkeleton, product }: ProductListItemLayoutProps) => (
    <li aria-busy={shouldShowProductActionSkeleton}>
        {product.fullName}: {currentCart.cart?.uuid ?? 'empty'}
    </li>
);
vi.mock('components/Blocks/Product/ProductsList/ProductListItemGridView', () => ({
    ProductListItemGridView: (props: ProductListItemLayoutProps) => <LayoutProbe {...props} />,
}));
vi.mock('components/Blocks/Product/ProductsList/ProductListItemListView', () => ({
    ProductListItemListView: (props: ProductListItemLayoutProps) => <LayoutProbe {...props} />,
}));

const products = Array.from({ length: 20 }, (_, index) => ({
    uuid: `product-${index}`,
    fullName: `Product ${index}`,
    isSellingDenied: false,
    isCurrentlyOutOfStock: false,
    isInquiryType: false,
})) as TypeListedProductFragment[];

describe.each(['grid', 'list'] as const)('ProductsListContent in %s view', (productListViewMode) => {
    const renderList = (overrides: Partial<Pick<TypeListedProductFragment, 'isSellingDenied'>> = {}) => (
        <ProductsListContent
            gtmMessageOrigin={GtmMessageOriginType.other}
            gtmProductListName={GtmProductListNameType.homepage_promo_products}
            productListViewMode={productListViewMode}
            products={products.map((product) => ({ ...product, ...overrides }))}
        />
    );

    beforeEach(() => {
        state.cart = { cart: undefined, isCartFetchingOrUnavailable: true };
        state.canCreateOrder = true;
    });

    test('reads cart state once for the entire list', () => {
        render(renderList());

        expect(screen.getAllByRole('listitem')).toHaveLength(20);
        expect(state.useCurrentCart).toHaveBeenCalledTimes(1);
    });

    test('updates every card when cart hydration, fetching and cart contents change', () => {
        const { rerender } = render(renderList());
        expect(screen.getAllByRole('listitem').every((item) => item.getAttribute('aria-busy') === 'true')).toBe(true);

        state.cart = { cart: null, isCartFetchingOrUnavailable: false };
        rerender(renderList());
        for (const item of screen.getAllByRole('listitem')) {
            expect(item).toHaveAttribute('aria-busy', 'false');
            expect(item).toHaveTextContent('empty');
        }

        state.cart = { cart: { uuid: 'updated-cart' } as CurrentCartType['cart'], isCartFetchingOrUnavailable: true };
        rerender(renderList());
        for (const item of screen.getAllByRole('listitem')) {
            expect(item).toHaveAttribute('aria-busy', 'true');
            expect(item).toHaveTextContent('updated-cart');
        }

        state.cart = { ...state.cart, isCartFetchingOrUnavailable: false };
        rerender(renderList());
        expect(screen.getAllByRole('listitem').every((item) => item.getAttribute('aria-busy') === 'false')).toBe(true);
    });

    test('does not wait for cart hydration when ordering is forbidden', () => {
        state.canCreateOrder = false;

        render(renderList());

        expect(screen.getAllByRole('listitem').every((item) => item.getAttribute('aria-busy') === 'false')).toBe(true);
    });

    test('does not wait for cart hydration for unsellable products', () => {
        render(renderList({ isSellingDenied: true }));

        expect(screen.getAllByRole('listitem').every((item) => item.getAttribute('aria-busy') === 'false')).toBe(true);
    });
});
