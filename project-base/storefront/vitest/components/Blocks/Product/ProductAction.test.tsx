import { render, screen } from '@testing-library/react';
import { PRODUCT_VARIANTS_ID, ProductAction } from 'components/Blocks/Product/ProductAction';
import { getButtonIconClassName } from 'components/Forms/Button/Button';
import type { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { TypeAvailabilityStatusEnum } from 'graphql/types';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { beforeEach, describe, expect, test, vi } from 'vitest';

type StubbedButtonProps = {
    buttonSize?: string;
    buttonVariant?: string;
};

let canCreateOrder = true;

vi.mock('components/providers/AuthorizationProvider', () => ({
    useAuthorization: () => ({ canCreateOrder }),
}));

vi.mock('components/Blocks/Product/AddToCart', () => ({
    AddToCart: ({ buttonSize, buttonVariant }: StubbedButtonProps) => (
        <button data-size={buttonSize} data-testid="add-to-cart" data-variant={buttonVariant} type="button">
            Add to cart
        </button>
    ),
    AddToCartContent: ({ buttonSize, buttonVariant }: StubbedButtonProps) => (
        <button data-size={buttonSize} data-testid="add-to-cart-with-cart" data-variant={buttonVariant} type="button">
            Add to cart
        </button>
    ),
}));

vi.mock('components/Blocks/Product/ProductInquiryButton', () => ({
    ProductInquiryButton: () => (
        <button data-testid="product-inquiry" type="button">
            Ask a question
        </button>
    ),
}));

vi.mock('components/Forms/Button/LinkButton', () => ({
    LinkButton: ({
        'aria-label': ariaLabel,
        children,
        href,
    }: {
        'aria-label': string;
        children: React.ReactNode;
        href: string;
    }) => (
        <a aria-label={ariaLabel} data-testid="choose-variant" href={href}>
            {children}
        </a>
    ),
}));

vi.mock('store/useSessionStore', () => ({
    useSessionStore: (selector: (state: { updatePortalContent: () => void }) => unknown) =>
        selector({ updatePortalContent: vi.fn() }),
}));

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({
        t: (key: string, options?: Record<string, string | number>) =>
            Object.entries(options ?? {}).reduce(
                (translatedKey, [optionKey, optionValue]) =>
                    translatedKey
                        .replaceAll(`{{ ${optionKey} }}`, String(optionValue))
                        .replaceAll(`{{${optionKey}}}`, String(optionValue)),
                key,
            ),
    }),
}));

const inStockProduct = {
    __typename: 'RegularProduct',
    id: 1,
    uuid: 'e2d0a3b0-2f5e-4f0a-9d2b-2f1f0b6c9a11',
    slug: '/product',
    fullName: 'Product',
    stockQuantity: 10,
    isAllowedNegativeStock: false,
    isSellingDenied: false,
    isCurrentlyOutOfStock: false,
    availableStoresCount: null,
    catalogNumber: 'CAT-1',
    isMainVariant: false,
    isInquiryType: false,
    unit: { __typename: 'Unit', name: 'pcs' },
    flags: [],
    mainImage: null,
    price: {
        __typename: 'ProductPrice',
        priceWithVat: '121.00',
        priceWithoutVat: '100.00',
        vatAmount: '21.00',
        isPriceFrom: false,
        percentageDiscount: null,
        basicPrice: { __typename: 'Price', priceWithVat: '121.00' },
    },
    expectedRestockingDate: null,
    availability: { __typename: 'Availability', name: 'In stock', status: TypeAvailabilityStatusEnum.InStock },
    brand: null,
    categories: [],
} satisfies TypeListedProductFragment;

const createProduct = (overrides: Partial<TypeListedProductFragment>) =>
    ({ ...inStockProduct, ...overrides }) as TypeListedProductFragment;

const renderProductAction = (
    product: TypeListedProductFragment,
    props: Partial<React.ComponentProps<typeof ProductAction>> = {},
) =>
    render(
        <ProductAction
            gtmMessageOrigin={GtmMessageOriginType.other}
            gtmProductListName={GtmProductListNameType.other}
            listIndex={0}
            product={product}
            {...props}
        />,
    );

const outOfStockProduct = createProduct({
    isCurrentlyOutOfStock: true,
    stockQuantity: 0,
    availability: {
        __typename: 'Availability',
        name: 'Out of stock',
        status: TypeAvailabilityStatusEnum.OutOfStock,
    },
});

const expectedRestockProduct = createProduct({
    stockQuantity: 0,
    isAllowedNegativeStock: true,
    availability: {
        __typename: 'Availability',
        name: 'Expected restock',
        status: TypeAvailabilityStatusEnum.ExpectedRestock,
    },
});

const sellingDeniedProduct = createProduct({ isSellingDenied: true });

const getWatchdogButtons = () => screen.queryAllByRole('button', { name: /Open watchdog popup/ });

describe('ProductAction', () => {
    beforeEach(() => {
        canCreateOrder = true;
    });

    test('links the main variant action directly to the variants section', () => {
        renderProductAction(
            createProduct({
                __typename: 'MainVariant',
                fullName: 'Television Philips [M]',
                isMainVariant: true,
                slug: '/television-philips-m',
            }),
        );

        expect(
            screen.getByRole('link', {
                name: 'Go to page with product variants of Television Philips [M]',
            }),
        ).toHaveAttribute('href', `/television-philips-m#${PRODUCT_VARIANTS_ID}`);
    });

    test('offers only add to cart for a product in stock', () => {
        renderProductAction(inStockProduct);

        expect(getWatchdogButtons()).toHaveLength(0);
        expect(screen.getByTestId('add-to-cart')).toHaveAttribute('data-variant', 'primary');
    });

    test('offers a single watchdog button and nothing else for a product out of stock', () => {
        renderProductAction(outOfStockProduct);

        expect(getWatchdogButtons()).toHaveLength(1);
        expect(screen.queryByTestId('add-to-cart')).not.toBeInTheDocument();
    });

    test('offers only add to cart for a product with an expected restocking date that can still be purchased', () => {
        renderProductAction(expectedRestockProduct);

        expect(getWatchdogButtons()).toHaveLength(0);
        expect(screen.getByTestId('add-to-cart')).toHaveAttribute('data-variant', 'primary');
    });

    test('offers only the message that a selling denied product cannot be purchased', () => {
        renderProductAction(sellingDeniedProduct);

        expect(getWatchdogButtons()).toHaveLength(0);
        expect(screen.getByText('This item can no longer be purchased')).toBeInTheDocument();
        expect(screen.queryByTestId('add-to-cart')).not.toBeInTheDocument();
    });

    test('offers only an inquiry button for an inquiry type variant', () => {
        renderProductAction(createProduct({ isInquiryType: true }));

        expect(getWatchdogButtons()).toHaveLength(0);
        expect(screen.getByTestId('product-inquiry')).toBeInTheDocument();
        expect(screen.queryByTestId('add-to-cart')).not.toBeInTheDocument();
    });

    test('offers nothing for an inquiry type variant that is out of stock', () => {
        renderProductAction(createProduct({ ...outOfStockProduct, isInquiryType: true }));

        expect(getWatchdogButtons()).toHaveLength(0);
        expect(screen.queryByTestId('product-inquiry')).not.toBeInTheDocument();
        expect(screen.queryByTestId('add-to-cart')).not.toBeInTheDocument();
    });

    test('offers only a link to the variants for a main variant', () => {
        renderProductAction(createProduct({ isMainVariant: true }));

        expect(getWatchdogButtons()).toHaveLength(0);
        expect(screen.getByTestId('choose-variant')).toBeInTheDocument();
        expect(screen.queryByTestId('add-to-cart')).not.toBeInTheDocument();
    });

    test('offers only a link to the variants for a main variant that could be watched', () => {
        renderProductAction(createProduct({ ...expectedRestockProduct, isMainVariant: true }));

        expect(getWatchdogButtons()).toHaveLength(0);
        expect(screen.getByTestId('choose-variant')).toBeInTheDocument();
    });

    test('offers nothing to a customer who cannot create an order', () => {
        canCreateOrder = false;

        renderProductAction(inStockProduct);

        expect(getWatchdogButtons()).toHaveLength(0);
        expect(screen.queryByTestId('add-to-cart')).not.toBeInTheDocument();
    });

    test('offers nothing to a customer who cannot create an order even when the goods could be watched', () => {
        canCreateOrder = false;

        renderProductAction(expectedRestockProduct);

        expect(getWatchdogButtons()).toHaveLength(0);
        expect(screen.queryByTestId('add-to-cart')).not.toBeInTheDocument();
    });

    test('offers the watchdog even to a customer who cannot create an order', () => {
        canCreateOrder = false;

        renderProductAction(outOfStockProduct);

        expect(getWatchdogButtons()).toHaveLength(1);
    });

    test('adds to cart against the current cart when one is passed', () => {
        renderProductAction(inStockProduct, {
            currentCart: { cart: null, isCartFetchingOrUnavailable: false },
        });

        expect(screen.getByTestId('add-to-cart-with-cart')).toBeInTheDocument();
        expect(screen.queryByTestId('add-to-cart')).not.toBeInTheDocument();
    });

    test('sizes the watchdog button the same as the add to cart button', () => {
        renderProductAction(expectedRestockProduct, {
            buttonSize: 'large',
            isWatchdogButtonShownWithPurchaseAction: true,
        });

        expect(screen.getByTestId('add-to-cart')).toHaveAttribute('data-size', 'large');
        expect(getWatchdogButtons()[0].querySelector('svg')).toHaveClass(getButtonIconClassName('large'));
    });

    describe('with the watchdog button shown next to the purchase action', () => {
        const props = { isWatchdogButtonShownWithPurchaseAction: true };

        test('offers both the watchdog and add to cart for a product that can still be purchased', () => {
            renderProductAction(expectedRestockProduct, props);

            expect(getWatchdogButtons()).toHaveLength(1);
            expect(screen.getByTestId('add-to-cart')).toHaveAttribute('data-variant', 'secondary');
            expect(screen.getAllByRole('button')[0]).toHaveAccessibleName(/Open watchdog popup/);
        });

        test('offers no watchdog button for an inquiry type variant', () => {
            renderProductAction(createProduct({ isInquiryType: true }), props);

            expect(getWatchdogButtons()).toHaveLength(0);
            expect(screen.getByTestId('product-inquiry')).toBeInTheDocument();
        });

        test('offers only the watchdog to a customer who cannot create an order', () => {
            canCreateOrder = false;

            renderProductAction(expectedRestockProduct, props);

            expect(getWatchdogButtons()).toHaveLength(1);
            expect(screen.queryByTestId('add-to-cart')).not.toBeInTheDocument();
        });

        test('offers the watchdog next to the message that a selling denied product cannot be purchased', () => {
            renderProductAction(sellingDeniedProduct, props);

            expect(getWatchdogButtons()).toHaveLength(1);
            expect(screen.getByText('This item can no longer be purchased')).toBeInTheDocument();
        });

        test('offers a single watchdog button for a product out of stock', () => {
            renderProductAction(outOfStockProduct, props);

            expect(getWatchdogButtons()).toHaveLength(1);
            expect(screen.queryByTestId('add-to-cart')).not.toBeInTheDocument();
        });

        test('offers no watchdog button for a product in stock', () => {
            renderProductAction(inStockProduct, props);

            expect(getWatchdogButtons()).toHaveLength(0);
            expect(screen.getByTestId('add-to-cart')).toHaveAttribute('data-variant', 'primary');
        });
    });
});
