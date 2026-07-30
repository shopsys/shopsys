import { screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { CartItemQuantityControls } from 'components/Blocks/Product/CartItemQuantityControls';
import { TypeCartItemFragment } from 'graphql/requests/cart/fragments/CartItemFragment.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { beforeEach, describe, expect, test, vi } from 'vitest';
import { renderWithTooltipProvider as render } from 'vitest/helpers/renderWithTooltipProvider';

const { addToCartMock, removeFromCartMock } = vi.hoisted(() => ({
    addToCartMock: vi.fn(),
    removeFromCartMock: vi.fn(),
}));

vi.mock('utils/cart/useAddToCart', () => ({
    useAddToCart: () => ({
        addToCart: addToCartMock,
        isAddingToCart: false,
    }),
}));

vi.mock('utils/cart/useRemoveFromCart', () => ({
    useRemoveFromCart: () => ({
        removeFromCart: removeFromCartMock,
        isRemovingFromCart: false,
    }),
}));

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    __esModule: true,
    default: () => ({
        t: (key: string, params?: Record<string, string | number>) =>
            Object.entries(params ?? {}).reduce((translatedKey, [name, value]) => {
                if (name === 'ns') {
                    return translatedKey;
                }

                return translatedKey.replace(`{{ ${name} }}`, String(value)).replace(`{{${name}}}`, String(value));
            }, key),
    }),
}));

vi.mock('utils/useDebounce', () => ({
    useDebounce: (value: unknown) => value,
}));

describe('CartItemQuantityControls', () => {
    const createCartItem = (quantity: number): TypeCartItemFragment =>
        ({
            uuid: 'cart-item-uuid',
            quantity,
            product: {
                uuid: 'product-uuid',
                fullName: 'Test product',
                isAllowedNegativeStock: false,
                stockQuantity: 10,
                unit: {
                    name: 'pcs',
                },
            },
        }) as unknown as TypeCartItemFragment;

    const getControlsElement = (cartItem: TypeCartItemFragment = createCartItem(1)) => (
        <CartItemQuantityControls
            cartItem={cartItem}
            gtmMessageOrigin={GtmMessageOriginType.other}
            gtmProductListName={GtmProductListNameType.other}
            listIndex={7}
        />
    );

    const renderControls = (cartItem: TypeCartItemFragment = createCartItem(1)) => render(getControlsElement(cartItem));

    beforeEach(() => {
        addToCartMock.mockResolvedValue({});
        removeFromCartMock.mockResolvedValue({});
        vi.clearAllMocks();
    });

    test('submits changed quantity directly from the spinbox', async () => {
        const user = userEvent.setup();
        renderControls();

        const increaseButton = screen.getByRole('button', { name: 'Increase quantity of Test product' });

        await user.click(increaseButton);

        await waitFor(() => {
            expect(addToCartMock).toHaveBeenCalledTimes(1);
        });
        expect(addToCartMock).toHaveBeenCalledWith('product-uuid', 2, 7, true);
    });

    test('uses product-specific accessibility labels', () => {
        const { getByLabelText, getByRole } = renderControls(createCartItem(2));

        expect(getByLabelText('Quantity of Test product')).toBeInTheDocument();
        const decreaseButton = getByRole('button', { name: 'Decrease quantity of Test product' });
        const increaseButton = getByRole('button', { name: 'Increase quantity of Test product' });

        expect(decreaseButton).not.toHaveAttribute('title');
        expect(increaseButton).not.toHaveAttribute('title');
    });

    test('uses product-specific remove label at minimum quantity', () => {
        const { getByRole } = renderControls(createCartItem(1));

        expect(getByRole('button', { name: 'Remove from cart product Test product' })).toBeInTheDocument();
    });

    test('announces quantity change after successful cart update', async () => {
        const user = userEvent.setup();
        const { getByRole } = renderControls();
        const liveRegion = getByRole('status');

        expect(liveRegion).toBeEmptyDOMElement();

        const increaseButton = screen.getByRole('button', { name: 'Increase quantity of Test product' });

        await user.click(increaseButton);

        await waitFor(() => {
            expect(getByRole('status')).toBe(liveRegion);
            expect(liveRegion).toHaveTextContent('Quantity of Test product updated to 2 pcs');
        });
    });

    test('removes the item when decreasing minimum quantity', async () => {
        const user = userEvent.setup();
        const cartItem = createCartItem(1);
        const { getByRole } = renderControls(cartItem);

        const decreaseButton = getByRole('button', { name: 'Remove from cart product Test product' });

        await user.click(decreaseButton);

        await waitFor(() => {
            expect(removeFromCartMock).toHaveBeenCalledTimes(1);
        });
        expect(removeFromCartMock).toHaveBeenCalledWith(cartItem, 7);
        expect(addToCartMock).not.toHaveBeenCalled();
    });

    test('submits a correction when value returns to the cart quantity after a pending submit', async () => {
        const user = userEvent.setup();
        const { getByRole } = renderControls(createCartItem(5));

        const decreaseButton = getByRole('button', { name: 'Decrease quantity of Test product' });
        const increaseButton = screen.getByRole('button', { name: 'Increase quantity of Test product' });

        await user.click(increaseButton);
        await user.click(decreaseButton);

        await waitFor(() => {
            expect(addToCartMock).toHaveBeenCalledTimes(2);
        });
        expect(addToCartMock).toHaveBeenNthCalledWith(1, 'product-uuid', 6, 7, true);
        expect(addToCartMock).toHaveBeenNthCalledWith(2, 'product-uuid', 5, 7, true);
    });

    test('submits the latest quantity after an overlapping cart update finishes', async () => {
        const user = userEvent.setup();
        let resolveFirstAddToCart: (value: unknown) => void = () => undefined;
        addToCartMock.mockReturnValueOnce(
            new Promise((resolve) => {
                resolveFirstAddToCart = resolve;
            }),
        );
        const { getByRole } = renderControls(createCartItem(1));

        const increaseButton = screen.getByRole('button', { name: 'Increase quantity of Test product' });

        await user.click(increaseButton);

        await waitFor(() => {
            expect(addToCartMock).toHaveBeenCalledTimes(1);
        });
        expect(addToCartMock).toHaveBeenNthCalledWith(1, 'product-uuid', 2, 7, true);

        const decreaseButton = getByRole('button', { name: 'Decrease quantity of Test product' });

        await user.click(decreaseButton);

        expect(addToCartMock).toHaveBeenCalledTimes(1);

        resolveFirstAddToCart({});

        await waitFor(() => {
            expect(addToCartMock).toHaveBeenCalledTimes(2);
        });
        expect(addToCartMock).toHaveBeenNthCalledWith(2, 'product-uuid', 1, 7, true);
    });

    test('keeps pending quantity during stale cart refetch and accepts the matching cart quantity later', async () => {
        const user = userEvent.setup();
        const { container, rerender } = renderControls(createCartItem(1));

        const input = container.querySelector('input[type="number"]') as HTMLInputElement;
        const increaseButton = screen.getByRole('button', { name: 'Increase quantity of Test product' });

        await user.click(increaseButton);

        await waitFor(() => {
            expect(addToCartMock).toHaveBeenCalledWith('product-uuid', 2, 7, true);
        });
        expect(input).toHaveValue(2);

        rerender(getControlsElement(createCartItem(3)));

        expect(input).toHaveValue(2);

        rerender(getControlsElement(createCartItem(2)));

        expect(input).toHaveValue(2);

        rerender(getControlsElement(createCartItem(4)));

        expect(input).toHaveValue(4);
    });

    test('restores previous quantity when the cart update fails', async () => {
        const user = userEvent.setup();
        addToCartMock.mockResolvedValueOnce(null);
        const { container, getByRole } = renderControls();
        const liveRegion = getByRole('status');

        const input = container.querySelector('input[type="number"]') as HTMLInputElement;
        const increaseButton = screen.getByRole('button', { name: 'Increase quantity of Test product' });

        await user.click(increaseButton);

        await waitFor(() => {
            expect(input).toHaveValue(1);
        });
        expect(getByRole('status')).toBe(liveRegion);
        expect(liveRegion).toBeEmptyDOMElement();
    });
});
