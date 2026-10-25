import { act, fireEvent, render, screen, waitFor, within } from '@testing-library/react';
import { ProductVariantsPopup } from 'components/Blocks/Popup/ProductVariantsPopup';
import { TypeAdditionalServiceFragment } from 'graphql/requests/additionalServices/fragments/AdditionalServiceFragment.generated';
import { TypeAvailabilityStatusEnum, TypeCartItemTypeEnum } from 'graphql/types';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { beforeEach, describe, expect, test, vi } from 'vitest';
import { createProduct } from '../../../helpers/productFixture';

const { useProductVariantsQuery, closePortalContent, addProduct, setCartItemAdditionalServices, testState } =
    vi.hoisted(() => ({
        useProductVariantsQuery: vi.fn(),
        closePortalContent: vi.fn(),
        addProduct: vi.fn(),
        setCartItemAdditionalServices: vi.fn(),
        testState: { cart: null as unknown, isSettingAdditionalServices: false },
    }));

const comparison = vi.hoisted(() => ({
    toggle: vi.fn(),
    callbacks: {} as { onProductAdded: () => void; onAddProductError: () => void },
    existing: new Set<string>(),
}));
vi.mock('utils/productLists/comparison/useComparison', () => ({
    useComparison: (callbacks: typeof comparison.callbacks) => {
        comparison.callbacks = callbacks;
        return {
            isProductInComparison: (uuid: string) => comparison.existing.has(uuid),
            toggleProductInComparison: comparison.toggle,
        };
    },
}));
vi.mock('utils/toasts/showSuccessMessage', () => ({ showSuccessMessage: vi.fn() }));

vi.mock('utils/cart/useCurrentCart', () => ({
    useCurrentCart: () => ({ cart: testState.cart, isCartFetchingOrUnavailable: false }),
}));
vi.mock('utils/cart/useSetCartItemAdditionalServices', () => ({
    useSetCartItemAdditionalServices: () => ({
        setCartItemAdditionalServices,
        isSettingAdditionalServices: testState.isSettingAdditionalServices,
    }),
}));
vi.mock('utils/formatting/useFormatPrice', () => ({
    useFormatPrice: () => (price: string | number) => `€${price}`,
}));

vi.mock('graphql/requests/products/queries/ProductVariantsQuery.generated', () => ({ useProductVariantsQuery }));
vi.mock('components/providers/AuthorizationProvider', () => ({ useAuthorization: () => ({ canCreateOrder: true }) }));
vi.mock('components/Basic/Image/Image', () => ({
    Image: ({ alt }: { alt: string }) => <span role="img" aria-label={alt} />,
}));
vi.mock('components/Layout/Popup/Popup', () => ({
    Popup: ({ children, title }: { children: React.ReactNode; title: string }) => (
        <div role="dialog" aria-label={title}>
            {children}
        </div>
    ),
}));
vi.mock('components/Forms/Button/LinkButton', () => ({
    LinkButton: ({
        children,
        href,
        onClick,
        'aria-label': ariaLabel,
    }: React.AnchorHTMLAttributes<HTMLAnchorElement>) => (
        <a href={href} aria-label={ariaLabel} onClick={onClick}>
            {children}
        </a>
    ),
}));
vi.mock('components/Blocks/Product/ProductPrice', () => ({
    ProductPrice: ({ productPrice }: { productPrice: { priceWithVat: string } }) => (
        <span>{productPrice.priceWithVat}</span>
    ),
}));
vi.mock('components/Blocks/Product/ProductAvailability', () => ({
    ProductAvailability: ({ availability }: { availability: { name: string } }) => <span>{availability.name}</span>,
}));
vi.mock('components/Blocks/Product/AddToCart', () => ({
    AddToCart: (props: { productUuid: string }) => (
        <button type="button" onClick={() => addProduct(props)}>
            Add to cart
        </button>
    ),
}));
vi.mock('components/Blocks/Product/ProductInquiryButton', () => ({
    ProductInquiryButton: ({ productUuid }: { productUuid: string }) => (
        <button type="button" data-uuid={productUuid}>
            Inquire
        </button>
    ),
}));
vi.mock('components/Blocks/Product/Watchdog/WatchDogButton', () => ({
    WatchDogButton: () => <button type="button">Watch availability</button>,
}));
vi.mock('store/useSessionStore', () => ({
    useSessionStore: (selector: (state: Record<string, unknown>) => unknown) => selector({ closePortalContent }),
}));
vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({
        t: (key: string, options?: Record<string, string>) =>
            key.replace('{{ productName }}', options?.productName ?? ''),
    }),
}));

const props = {
    productUuid: 'main-product',
    productSlug: '/main-product',
    productName: 'Main product',
    gtmMessageOrigin: GtmMessageOriginType.other,
    gtmProductListName: GtmProductListNameType.category_detail,
    listIndex: 4,
};

const variants = [
    {
        ...createProduct('blue-variant'),
        __typename: 'Variant',
        fullName: 'Blue variant',
        price: { ...createProduct('blue-variant').price, priceWithVat: '242' },
    },
    { ...createProduct('red-variant'), __typename: 'Variant', fullName: 'Red variant', isInquiryType: true },
    {
        ...createProduct('sold-out'),
        __typename: 'Variant',
        fullName: 'Sold out variant',
        isCurrentlyOutOfStock: true,
        stockQuantity: 0,
        availability: { name: 'Out of stock', status: TypeAvailabilityStatusEnum.OutOfStock },
    },
    { ...createProduct('denied'), __typename: 'Variant', fullName: 'Denied variant', isSellingDenied: true },
];

const additionalService: TypeAdditionalServiceFragment = {
    __typename: 'AdditionalService',
    id: 1,
    uuid: 'extended-warranty',
    catnum: 'WARRANTY',
    name: 'Extended warranty',
    description: null,
    deliveryDaysExtension: null,
    mainImage: null,
    price: { __typename: 'Price', priceWithVat: '10', priceWithoutVat: '8', vatAmount: '2' },
};

const setVariantsWithServices = () =>
    setQueryResult({
        data: {
            product: {
                __typename: 'MainVariant',
                variants: variants.slice(0, 2).map((variant) => ({
                    ...variant,
                    isInquiryType: false,
                    additionalServices: [additionalService],
                })),
            },
        },
    });

const setQueryResult = (overrides = {}) =>
    useProductVariantsQuery.mockReturnValue([
        {
            data: {
                product: {
                    __typename: 'MainVariant',
                    variants: variants.map((variant) => ({ ...variant, additionalServices: [] })),
                },
            },
            fetching: false,
            error: undefined,
            ...overrides,
        },
    ]);

describe('ProductVariantsPopup', () => {
    beforeEach(() => {
        comparison.existing.clear();
        comparison.toggle.mockClear();
        closePortalContent.mockClear();
        testState.cart = null;
        testState.isSettingAdditionalServices = false;
        setCartItemAdditionalServices.mockResolvedValue(null);
        setQueryResult();
    });

    test('offers variant prices, availability and purchase actions with the original listing context', async () => {
        render(<ProductVariantsPopup {...props} />);

        const rows = await screen.findAllByRole('listitem');
        expect(within(rows[0]).getByText('Blue variant')).toBeInTheDocument();
        expect(within(rows[0]).getByText('242')).toBeInTheDocument();
        expect(within(rows[0]).getByText('In stock')).toBeInTheDocument();

        fireEvent.click(within(rows[0]).getByRole('button', { name: 'Add to cart' }));

        expect(addProduct).toHaveBeenCalledWith(
            expect.objectContaining({
                productUuid: 'blue-variant',
                gtmMessageOrigin: props.gtmMessageOrigin,
                gtmProductListName: props.gtmProductListName,
                listIndex: props.listIndex,
                maxQuantity: 10,
            }),
        );
        expect(within(rows[1]).getByRole('button', { name: 'Inquire' })).toHaveAttribute('data-uuid', 'red-variant');
        expect(within(rows[1]).queryByRole('button', { name: 'Add to cart' })).not.toBeInTheDocument();
        expect(within(rows[2]).getByText('Out of stock')).toBeInTheDocument();
        expect(within(rows[2]).queryByRole('button', { name: 'Add to cart' })).not.toBeInTheDocument();
        expect(within(rows[3]).getByText('This item can no longer be purchased')).toBeInTheDocument();
        expect(within(rows[3]).queryByRole('button', { name: 'Add to cart' })).not.toBeInTheDocument();
    });

    test('shows services immediately, keeps variant selections separate and persists them when adding the product', async () => {
        const updatedCart = { uuid: 'updated-cart', items: [] };
        setCartItemAdditionalServices.mockResolvedValue(updatedCart);
        setVariantsWithServices();
        render(<ProductVariantsPopup {...props} />);
        const blueRow = (await screen.findByRole('heading', { name: 'Blue variant' })).closest('li')!;
        const redRow = screen.getByRole('heading', { name: 'Red variant' }).closest('li')!;
        const blueService = within(blueRow).getByRole('checkbox', { name: /Extended warranty/ });
        const redService = within(redRow).getByRole('checkbox', { name: /Extended warranty/ });

        expect(blueService).not.toBeChecked();
        expect(redService).not.toBeChecked();
        fireEvent.click(blueService);

        expect(blueService).toBeChecked();
        expect(redService).not.toBeChecked();
        fireEvent.click(within(blueRow).getByRole('button', { name: 'Add to cart' }));
        await act(async () => {
            const cart = await addProduct.mock.calls[0][0].onProductAddedToCart({ uuid: 'new-cart-item' });
            expect(cart).toBe(updatedCart);
        });
        expect(setCartItemAdditionalServices).toHaveBeenCalledWith('new-cart-item', [additionalService.uuid]);
    });

    test('reflects existing cart services and updates the correct cart item', async () => {
        const cartItem = {
            uuid: 'existing-cart-item',
            type: TypeCartItemTypeEnum.Product,
            product: { uuid: 'blue-variant' },
            quantity: 3,
            additionalServices: [additionalService],
        };
        testState.cart = { items: [cartItem] };
        setVariantsWithServices();
        render(<ProductVariantsPopup {...props} />);
        const row = (await screen.findByRole('heading', { name: 'Blue variant' })).closest('li')!;
        const service = within(row).getByRole('checkbox', { name: /Extended warranty/ });

        expect(service).toBeChecked();
        expect(service).toHaveAccessibleDescription('Price: €30');
        fireEvent.click(service);

        expect(setCartItemAdditionalServices).toHaveBeenCalledWith('existing-cart-item', [], {
            cartItem,
            toggledService: additionalService,
            isServiceAdded: false,
        });
    });

    test('keeps services locked throughout adding the variant and unlocks them if the add finishes', async () => {
        setVariantsWithServices();
        render(<ProductVariantsPopup {...props} />);
        const row = (await screen.findByRole('heading', { name: 'Blue variant' })).closest('li')!;
        const checkbox = within(row).getByRole('checkbox', { name: /Extended warranty/ });
        fireEvent.click(checkbox);
        fireEvent.click(within(row).getByRole('button', { name: 'Add to cart' }));

        act(() => addProduct.mock.calls[0][0].onAddToCartFlowStateChange(true));

        expect(checkbox).toBeChecked();
        expect(checkbox).toBeDisabled();
        act(() => addProduct.mock.calls[0][0].onAddToCartFlowStateChange(false));
        expect(checkbox).toBeEnabled();
        expect(checkbox).toBeChecked();
    });

    test('disables service selection while the cart update is pending', async () => {
        testState.isSettingAdditionalServices = true;
        setVariantsWithServices();

        render(<ProductVariantsPopup {...props} />);

        expect(await screen.findAllByRole('checkbox')).toHaveLength(2);
        for (const checkbox of screen.getAllByRole('checkbox')) {
            expect(checkbox).toBeDisabled();
        }
    });

    test('refreshes variant data when the popup mounts', async () => {
        render(<ProductVariantsPopup {...props} />);

        await waitFor(() =>
            expect(useProductVariantsQuery).toHaveBeenCalledWith({
                variables: { uuid: 'main-product' },
                requestPolicy: 'network-only',
            }),
        );
    });

    test('does not offer stale cached variants while refreshing', async () => {
        setQueryResult({ fetching: true });

        render(<ProductVariantsPopup {...props} />);

        await waitFor(() => expect(useProductVariantsQuery).toHaveBeenCalled());
        expect(screen.getByRole('status')).toHaveAccessibleName('Loading options');
        expect(screen.queryByRole('listitem')).not.toBeInTheDocument();
    });

    test('does not offer cached variants after a failed refresh', async () => {
        setQueryResult({ error: new Error('Network failure') });

        render(<ProductVariantsPopup {...props} />);

        expect(await screen.findByRole('alert')).toHaveTextContent(
            'Product variants could not be loaded. Please try again later.',
        );
        expect(screen.queryByRole('listitem')).not.toBeInTheDocument();
    });

    test('shows an empty state when no variants remain', async () => {
        setQueryResult({ data: { product: { __typename: 'MainVariant', variants: [] } } });

        render(<ProductVariantsPopup {...props} />);

        expect(
            await screen.findByText('Currently, it is not possible to purchase any variant of this product.'),
        ).toBeInTheDocument();
    });

    test.each([null, { __typename: 'RegularProduct' }])('handles a missing or changed product', async (product) => {
        setQueryResult({ data: { product } });

        render(<ProductVariantsPopup {...props} />);

        expect(await screen.findByRole('alert')).toBeInTheDocument();
    });

    test('closes the selector when navigating to the product detail', () => {
        render(<ProductVariantsPopup {...props} />);

        fireEvent.click(screen.getByRole('link', { name: 'Go to page with product variants of Main product' }));

        expect(closePortalContent).toHaveBeenCalledOnce();
    });
    test('compares a concrete variant without cart actions and keeps the selector open until success', async () => {
        const onVariantCompared = vi.fn();
        render(<ProductVariantsPopup {...props} onVariantCompared={onVariantCompared} />);
        const blue = await screen.findByRole('button', { name: 'Add to comparison product Blue variant' });
        expect(screen.queryByRole('button', { name: 'Add to cart' })).not.toBeInTheDocument();
        expect(screen.queryByRole('checkbox')).not.toBeInTheDocument();

        fireEvent.click(blue);
        fireEvent.click(screen.getByRole('button', { name: 'Add to comparison product Red variant' }));

        expect(comparison.toggle).toHaveBeenCalledTimes(1);
        expect(comparison.toggle).toHaveBeenCalledWith(
            expect.objectContaining({ uuid: 'blue-variant' }),
            props.gtmProductListName,
            props.listIndex,
        );
        expect(onVariantCompared).not.toHaveBeenCalled();
        expect(closePortalContent).not.toHaveBeenCalled();
        expect(blue).toBeDisabled();

        act(() => comparison.callbacks.onAddProductError());
        expect(blue).toBeEnabled();
        expect(onVariantCompared).not.toHaveBeenCalled();
        fireEvent.click(blue);
        act(() => comparison.callbacks.onProductAdded());
        expect(onVariantCompared).toHaveBeenCalledOnce();
        expect(closePortalContent).toHaveBeenCalledOnce();
    });

    test('selects an already compared variant without removing it', async () => {
        comparison.existing.add('blue-variant');
        const onVariantCompared = vi.fn();
        render(<ProductVariantsPopup {...props} onVariantCompared={onVariantCompared} />);

        fireEvent.click(await screen.findByRole('button', { name: 'Add to comparison product Blue variant' }));

        expect(comparison.toggle).not.toHaveBeenCalled();
        expect(onVariantCompared).toHaveBeenCalledOnce();
        expect(closePortalContent).toHaveBeenCalledOnce();
    });

    test('does not close a different popup when a pending comparison completes after dismissal', async () => {
        const onVariantCompared = vi.fn();
        const { unmount } = render(<ProductVariantsPopup {...props} onVariantCompared={onVariantCompared} />);
        fireEvent.click(await screen.findByRole('button', { name: 'Add to comparison product Blue variant' }));
        unmount();

        act(() => comparison.callbacks.onProductAdded());

        expect(onVariantCompared).toHaveBeenCalledOnce();
        expect(closePortalContent).not.toHaveBeenCalled();
    });
});
