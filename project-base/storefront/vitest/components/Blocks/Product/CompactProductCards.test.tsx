import { fireEvent, render, screen } from '@testing-library/react';
import { CompactProductItemProps } from 'components/Blocks/Product/ProductsList/CompactProductListItem';
import { ProductItemProps } from 'components/Blocks/Product/ProductsList/ProductListItem';
import {
    CompactProductsListContent,
    ProductsListContent,
} from 'components/Blocks/Product/ProductsList/ProductsListContent';
import { TypeCompactProductFragment } from 'graphql/requests/products/fragments/CompactProductFragment.generated';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { TypeAvailabilityStatusEnum, TypeProductTypeEnum } from 'graphql/types';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { ReactNode } from 'react';
import { describe, expect, expectTypeOf, test, vi } from 'vitest';

const mocks = vi.hoisted(() => ({
    cart: vi.fn(() => ({ cart: undefined, isCartFetchingOrUnavailable: false })),
    wishlist: vi.fn(() => ({ isProductInWishlist: () => false, toggleProductInWishlist: vi.fn() })),
    comparison: vi.fn(() => ({ isProductInComparison: () => false, toggleProductInComparison: vi.fn() })),
    productClick: vi.fn(),
}));
vi.mock('utils/cart/useCurrentCart', () => ({ useCurrentCart: mocks.cart }));
vi.mock('utils/productLists/wishlist/useWishlist', () => ({ useWishlist: mocks.wishlist }));
vi.mock('utils/productLists/comparison/useComparison', () => ({ useComparison: mocks.comparison }));
vi.mock('utils/queryParams/useCurrentPageQuery', () => ({ useCurrentPageQuery: () => 1 }));
vi.mock('components/providers/AuthorizationProvider', () => ({
    useAuthorization: () => ({ canCreateOrder: true, canSeePrices: true }),
}));
vi.mock('components/providers/DomainConfigProvider', () => ({
    useDomainConfig: () => ({ url: 'https://example.com' }),
}));
vi.mock('utils/i18n/useTranslationWrapper', () => ({ default: () => ({ t: (key: string) => key }) }));
vi.mock('gtm/handlers/onGtmProductClickEventHandler', () => ({ onGtmProductClickEventHandler: mocks.productClick }));
vi.mock('components/Basic/ExtendedNextLink/ExtendedNextLink', () => ({
    ExtendedNextLink: ({
        children,
        href,
        tabIndex,
        onMouseUp,
    }: {
        children: ReactNode;
        href: string;
        tabIndex?: number;
        onMouseUp?: () => void;
    }) => (
        <a href={href} tabIndex={tabIndex} onMouseUp={onMouseUp}>
            {children}
        </a>
    ),
}));
vi.mock('components/Blocks/Product/ProductsList/ProductListItemImage', () => ({
    ProductListItemImage: () => <span>Product image</span>,
    getProductListItemImageSize: () => 94,
}));
vi.mock('components/Blocks/Product/ProductPrice', () => ({ ProductPrice: () => <span>Product price</span> }));
vi.mock('components/Blocks/Product/ProductFlags', () => ({ ProductFlags: () => null }));
vi.mock('components/Blocks/Product/ProductAvailability', () => ({
    ProductAvailability: () => <span>Store availability</span>,
}));
vi.mock('components/Blocks/ProductReviews/ProductListReviewsSummaryLink', () => ({
    ProductListReviewsSummaryLink: () => null,
}));
vi.mock('components/Blocks/Product/ProductsList/ProductListItemActions', () => ({
    ProductListItemButtons: () => <span>List buttons</span>,
    ProductListItemAddToCart: () => <span>Add to cart</span>,
}));

const compactProduct = {
    __typename: 'RegularProduct',
    id: 42,
    uuid: 'f7888ef5-ae16-4f5c-b98d-4a6c947a9f71',
    slug: '/test-product',
    fullName: 'Test product',
    mainCategory: null,
    isSellingDenied: false,
    flags: [],
    mainImage: { __typename: 'Image', name: null, url: '/main.jpg' },
    price: {
        __typename: 'ProductPrice',
        priceWithVat: '121',
        priceWithoutVat: '100',
        vatAmount: '21',
        isPriceFrom: false,
        percentageDiscount: null,
        basicPrice: {
            __typename: 'Price',
            priceWithVat: '121',
        },
    },
    expectedRestockingDate: null,
    availability: { __typename: 'Availability', name: 'In stock', status: TypeAvailabilityStatusEnum.InStock },
    catalogNumber: 'TEST-42',
    brand: null,
    categories: [{ __typename: 'Category', name: 'Test category' }],
    isMainVariant: false,
    reviewsSummary: null,
    productType: TypeProductTypeEnum.Basic,
} satisfies TypeCompactProductFragment;

const fullProduct: TypeListedProductFragment = {
    ...compactProduct,
    stockQuantity: 10,
    isAllowedNegativeStock: false,
    isCurrentlyOutOfStock: false,
    availableStoresCount: 1,
    isPersonalPickupOnly: false,
    isInquiryType: false,
    unit: { __typename: 'Unit', name: 'pcs' },
};
const listProps = {
    gtmProductListName: GtmProductListNameType.autocomplete_favorites,
    gtmMessageOrigin: GtmMessageOriginType.other,
};

describe('product card data boundaries', () => {
    test('compact cards render and track clicks without initializing shopping hooks', () => {
        const onClick = vi.fn();
        render(
            <CompactProductsListContent
                {...listProps}
                products={[compactProduct]}
                productItemProps={{ visibleItemsConfig: { price: true }, onClick }}
            />,
        );

        expect(screen.getByRole('heading', { name: 'Test product' })).toBeInTheDocument();
        expect(screen.getByText('Product price')).toBeInTheDocument();
        expect(screen.queryByText('Add to cart')).not.toBeInTheDocument();
        expect(screen.queryByText('List buttons')).not.toBeInTheDocument();
        expect(screen.queryByText('Store availability')).not.toBeInTheDocument();
        expect(mocks.cart).not.toHaveBeenCalled();
        expect(mocks.wishlist).not.toHaveBeenCalled();
        expect(mocks.comparison).not.toHaveBeenCalled();

        fireEvent.mouseUp(screen.getByRole('link'));

        expect(mocks.productClick).toHaveBeenCalledWith(
            compactProduct,
            listProps.gtmProductListName,
            0,
            'https://example.com',
            false,
        );
        expect(onClick).toHaveBeenCalledWith(compactProduct, 0);
    });

    test('full cards keep shopping controls and the same product link', () => {
        render(<ProductsListContent {...listProps} products={[fullProduct]} />);

        expect(screen.getByRole('link')).toHaveAttribute('href', compactProduct.slug);
        expect(screen.getByText('Add to cart')).toBeInTheDocument();
        expect(screen.getByText('List buttons')).toBeInTheDocument();
        expect(screen.getByText('Store availability')).toBeInTheDocument();
        expect(mocks.cart).toHaveBeenCalled();
        expect(mocks.wishlist).toHaveBeenCalled();
        expect(mocks.comparison).toHaveBeenCalled();
    });

    test('compact cards retain keyboard focus boundaries and cannot enable purchase controls', () => {
        render(
            <CompactProductsListContent
                {...listProps}
                products={[compactProduct]}
                keyboardFocusableProductIndices={[]}
            />,
        );

        expect(screen.getByRole('link')).toHaveAttribute('tabindex', '-1');
        expectTypeOf<TypeCompactProductFragment>().not.toExtend<ProductItemProps['product']>();
        expectTypeOf<'addToCart'>().not.toExtend<keyof NonNullable<CompactProductItemProps['visibleItemsConfig']>>();
    });
});
