import { act, render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { CartProductGifts } from 'components/Pages/Cart/CartProductGifts';
import { TypeCartFragment } from 'graphql/requests/cart/fragments/CartFragment.generated';
import { CartProductGift } from 'utils/cart/groupCartItemsWithGifts';
import { beforeEach, describe, expect, test, vi } from 'vitest';

const restoreCartGiftMutation = vi.hoisted(() => vi.fn());

vi.mock('components/Basic/Image/Image', () => ({ Image: () => null }));
vi.mock('components/Blocks/Product/ProductAvailability', () => ({ ProductAvailability: () => null }));
vi.mock('components/Pages/Cart/CartItemPrice', () => ({ CartItemPrice: () => null }));
vi.mock('components/providers/DomainConfigProvider', () => ({ useDomainConfig: () => ({ domainId: 1 }) }));
vi.mock('graphql/requests/cart/mutations/RestoreCartGiftMutation.generated', () => ({
    useRestoreCartGiftMutation: () => [{ fetching: false }, restoreCartGiftMutation],
}));
vi.mock('store/usePersistStore', () => ({
    usePersistStore: (selector: (state: { cartUuid: string }) => unknown) => selector({ cartUuid: 'cart' }),
}));
vi.mock('utils/formatting/useFormatPrice', () => ({ useFormatPrice: () => (price: string | number) => `€${price}` }));
vi.mock('utils/i18n/useTranslationWrapper', () => ({ default: () => ({ t: (key: string) => key }) }));
vi.mock('utils/useBroadcastChannel', () => ({ dispatchBroadcastChannel: vi.fn() }));

const product = {
    uuid: 'gift',
    name: 'Coffee beans',
    fullName: 'Coffee beans 250 g',
    mainCategory: null,
    mainImage: null,
    images: [],
    isAllowedNegativeStock: true,
    stockQuantity: null,
    unit: { name: 'pcs' },
    giftPrice: {
        __typename: 'ProductPrice' as const,
        priceWithVat: '0',
        priceWithoutVat: '0',
        vatAmount: '0',
        isPriceFrom: false,
        nextPriceChange: null,
        percentageDiscount: null,
        basicPrice: { priceWithVat: '0', priceWithoutVat: '0', vatAmount: '0' },
    },
};
const addedGift: CartProductGift = {
    state: 'added',
    cartItem: { uuid: 'gift-cart-item', quantity: 1, product } as unknown as TypeCartFragment['items'][number],
    listIndex: 1,
    relatedProducts: [],
};
const removedGift: CartProductGift = {
    state: 'removed',
    removedGift: { product, quantity: 1 },
    relatedProducts: [],
};

const deferredRestore = (success = true) => {
    let complete: () => void = () => undefined;
    const request = new Promise((resolve) => {
        complete = () => resolve({ data: success ? { RestoreCartGift: { uuid: 'cart' } } : null });
    });
    restoreCartGiftMutation.mockReturnValueOnce(request);

    return complete;
};

describe('CartProductGifts', () => {
    beforeEach(() => {
        restoreCartGiftMutation.mockReset();
    });

    test.each([
        { quantity: 1, unitName: 'pcs' },
        { quantity: 3, unitName: 'pcs' },
        { quantity: 2, unitName: 'm²' },
    ])('labels the removed gift price per $unitName for quantity $quantity', ({ quantity, unitName }) => {
        const paidGift: CartProductGift = {
            ...removedGift,
            removedGift: {
                quantity,
                product: {
                    ...product,
                    unit: { name: unitName },
                    giftPrice: { ...product.giftPrice, priceWithVat: '1.16' },
                },
            },
        };

        render(
            <CartProductGifts
                gifts={[paidGift]}
                isRemovingGift={false}
                parentCartItemUuid="parent"
                onRemoveGift={vi.fn()}
            />,
        );

        expect(screen.getByText(`/ ${unitName}`)).toBeInTheDocument();
        expect(screen.getByRole('checkbox', { name: /Coffee beans/ })).toHaveAccessibleName(
            new RegExp(`\\+ €1\\.16 / ${unitName}`),
        );
    });

    test('restores keyboard focus after removing the gift', async () => {
        const user = userEvent.setup();
        const onRemoveGift = vi.fn();
        const { rerender } = render(
            <CartProductGifts
                gifts={[addedGift]}
                isRemovingGift={false}
                parentCartItemUuid="parent"
                onRemoveGift={onRemoveGift}
            />,
        );
        const checkbox = screen.getByRole('checkbox', { name: product.fullName });
        checkbox.focus();

        await user.keyboard('[Space]');
        rerender(
            <CartProductGifts
                gifts={[addedGift]}
                isRemovingGift
                parentCartItemUuid="parent"
                onRemoveGift={onRemoveGift}
            />,
        );
        // jsdom does not blur a focused input when it becomes disabled, unlike browsers.
        checkbox.blur();
        rerender(
            <CartProductGifts
                gifts={[removedGift]}
                isRemovingGift={false}
                parentCartItemUuid="parent"
                onRemoveGift={onRemoveGift}
            />,
        );

        expect(checkbox).not.toBeChecked();
        expect(checkbox).toHaveFocus();
        expect(onRemoveGift).toHaveBeenCalledWith(addedGift.cartItem, addedGift.listIndex);
    });

    test.each([true, false])('restores keyboard focus after restoring the gift (success: %s)', async (success) => {
        const user = userEvent.setup();
        const completeRestore = deferredRestore(success);
        const props = { isRemovingGift: false, parentCartItemUuid: 'parent', onRemoveGift: vi.fn() };
        const { rerender } = render(<CartProductGifts {...props} gifts={[removedGift]} />);
        const checkbox = screen.getByRole('checkbox', { name: product.fullName });
        checkbox.focus();

        await user.keyboard('[Space]');
        expect(checkbox).toBeDisabled();
        checkbox.blur();
        rerender(<CartProductGifts {...props} gifts={[success ? addedGift : removedGift]} />);
        await act(async () => completeRestore());

        expect(checkbox).not.toBeDisabled();
        expect(checkbox).toHaveFocus();
        expect(checkbox).toHaveProperty('checked', success);
    });

    test('does not steal focus when the customer moves to another control during restoration', async () => {
        const user = userEvent.setup();
        const completeRestore = deferredRestore();
        render(
            <>
                <CartProductGifts
                    gifts={[removedGift]}
                    isRemovingGift={false}
                    parentCartItemUuid="parent"
                    onRemoveGift={vi.fn()}
                />
                <button type="button">Continue</button>
            </>,
        );
        const checkbox = screen.getByRole('checkbox', { name: product.fullName });
        checkbox.focus();

        await user.keyboard('[Space]');
        const continueButton = screen.getByRole('button', { name: 'Continue' });
        continueButton.focus();
        await act(async () => completeRestore());

        expect(continueButton).toHaveFocus();
    });
});
