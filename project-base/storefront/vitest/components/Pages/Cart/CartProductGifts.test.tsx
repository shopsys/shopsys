import { act, render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { CartProductGifts } from 'components/Pages/Cart/CartProductGifts';
import { TypeCartFragment } from 'graphql/requests/cart/fragments/CartFragment.generated';
import { TypeCartItemTypeEnum } from 'graphql/types';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { CartProductGift } from 'utils/cart/groupCartItemsWithGifts';
import { beforeEach, describe, expect, test, vi } from 'vitest';

const restoreCartGiftMutation = vi.hoisted(() => vi.fn());
const onGtmRestoreCartGiftEventHandler = vi.hoisted(() => vi.fn());

vi.mock('components/Basic/Image/Image', () => ({ Image: () => null }));
vi.mock('components/Blocks/Product/ProductAvailability', () => ({ ProductAvailability: () => null }));
vi.mock('components/Pages/Cart/CartItemPrice', () => ({ CartItemPrice: () => null }));
vi.mock('components/providers/AuthorizationProvider', () => ({ useAuthorization: () => ({ canSeePrices: true }) }));
vi.mock('components/providers/DomainConfigProvider', () => ({
    useDomainConfig: () => ({ domainId: 1, url: 'https://example.com', currencyCode: 'EUR' }),
}));
vi.mock('graphql/requests/cart/mutations/RestoreCartGiftMutation.generated', () => ({
    useRestoreCartGiftMutation: () => [{ fetching: false }, restoreCartGiftMutation],
}));
vi.mock('gtm/handlers/onGtmRestoreCartGiftEventHandler', () => ({ onGtmRestoreCartGiftEventHandler }));
vi.mock('gtm/utils/getGtmMappedCart', () => ({ getGtmMappedCart: () => null }));
vi.mock('store/usePersistStore', () => ({
    usePersistStore: (selector: (state: { cartUuid: string }) => unknown) => selector({ cartUuid: 'cart' }),
}));
vi.mock('utils/auth/useIsUserLoggedIn', () => ({ useIsUserLoggedIn: () => false }));
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
    removedGift: { giftProduct: product, mainProduct: { uuid: 'parent-product' } },
    relatedProducts: [],
};

const deferredRestore = (success = true) => {
    let complete: () => void = () => undefined;
    const request = new Promise((resolve) => {
        complete = () => resolve({ data: success ? { RestoreCartGift: { uuid: 'cart', items: [] } } : null });
    });
    restoreCartGiftMutation.mockReturnValueOnce(request);

    return complete;
};

describe('CartProductGifts', () => {
    beforeEach(() => {
        restoreCartGiftMutation.mockReset();
        onGtmRestoreCartGiftEventHandler.mockReset();
    });

    test.each([{ unitName: 'pcs' }, { unitName: 'm²' }])(
        'labels the removed gift price per $unitName',
        ({ unitName }) => {
            const paidGift: CartProductGift = {
                ...removedGift,
                removedGift: {
                    mainProduct: { uuid: 'parent-product' },
                    giftProduct: {
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
        },
    );

    test('restores only the selected parent and gift pair', async () => {
        restoreCartGiftMutation.mockResolvedValue({ data: { RestoreCartGift: { uuid: 'cart', items: [] } } });
        render(
            <CartProductGifts
                gifts={[removedGift]}
                isRemovingGift={false}
                parentCartItemUuid="parent"
                onRemoveGift={vi.fn()}
            />,
        );

        await userEvent.click(screen.getByRole('checkbox', { name: product.fullName }));

        expect(restoreCartGiftMutation).toHaveBeenCalledWith({
            input: { cartUuid: 'cart', giftProductUuid: product.uuid, mainProductUuid: 'parent-product' },
        });
    });

    test('reports the restored gift of the selected parent to GTM', async () => {
        const restoredGiftCartItem = {
            uuid: 'restored-gift-cart-item',
            type: TypeCartItemTypeEnum.ProductGift,
            quantity: 2,
            product,
            relatedItems: [],
        };
        const otherParentGiftCartItem = { ...restoredGiftCartItem, uuid: 'other-parent-gift-cart-item' };
        const createParentCartItem = (productUuid: string, giftCartItemUuid: string) => ({
            uuid: `${productUuid}-cart-item`,
            type: TypeCartItemTypeEnum.Product,
            quantity: 2,
            product: { uuid: productUuid, fullName: productUuid },
            relatedItems: [{ uuid: giftCartItemUuid }],
        });
        restoreCartGiftMutation.mockResolvedValue({
            data: {
                RestoreCartGift: {
                    uuid: 'cart',
                    promoCodes: [],
                    items: [
                        createParentCartItem('other-parent-product', otherParentGiftCartItem.uuid),
                        otherParentGiftCartItem,
                        createParentCartItem('parent-product', restoredGiftCartItem.uuid),
                        restoredGiftCartItem,
                    ],
                },
            },
        });
        render(
            <CartProductGifts
                gifts={[removedGift]}
                isRemovingGift={false}
                parentCartItemUuid="parent"
                onRemoveGift={vi.fn()}
            />,
        );

        await userEvent.click(screen.getByRole('checkbox', { name: product.fullName }));

        await vi.waitFor(() =>
            expect(onGtmRestoreCartGiftEventHandler).toHaveBeenCalledExactlyOnceWith(
                restoredGiftCartItem,
                'EUR',
                3,
                GtmProductListNameType.cart,
                'https://example.com',
                false,
                null,
            ),
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
