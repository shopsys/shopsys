import { cleanup, fireEvent, render, screen } from '@testing-library/react';
import { CartInHeaderList } from 'components/Layout/Header/Cart/CartInHeaderList';
import { TypeCartItemFragment } from 'graphql/requests/cart/fragments/CartItemFragment.generated';
import { TypeCartItemTypeEnum } from 'graphql/types';
import { CartProductGift } from 'utils/cart/groupCartItemsWithGifts';
import { afterEach, beforeAll, beforeEach, describe, expect, test, vi } from 'vitest';

const { useFocusTrap, useCurrentCartMock, removeFromCart } = vi.hoisted(() => ({
    useFocusTrap: vi.fn(),
    useCurrentCartMock: vi.fn(),
    removeFromCart: vi.fn(),
}));

vi.mock('components/Blocks/FreeTransport/FreeTransportRange', () => ({
    FreeTransportRange: () => <div>Free transport</div>,
}));

vi.mock('components/Forms/Button/LinkButton', () => ({
    LinkButton: ({ children }: React.PropsWithChildren) => <a href="/cart">{children}</a>,
}));

vi.mock('components/Layout/Header/Cart/CartInHeaderListItem', () => ({
    CartInHeaderListItem: ({
        cartItem,
        gifts,
        onRemoveFromCart,
    }: {
        cartItem: TypeCartItemFragment;
        gifts: CartProductGift[];
        onRemoveFromCart: React.MouseEventHandler<HTMLButtonElement>;
    }) => (
        <li>
            {cartItem.product.fullName}
            {gifts.map(
                (gift) =>
                    gift.state === 'added' && (
                        <span key={gift.cartItem.uuid}>
                            {gift.cartItem.product.fullName}:{' '}
                            {gift.relatedProducts.map((product) => product.fullName).join(', ')}
                        </span>
                    ),
            )}
            <button type="button" onClick={onRemoveFromCart}>
                Remove {cartItem.product.fullName}
            </button>
        </li>
    ),
}));

vi.mock('components/providers/DomainConfigProvider', () => ({
    useDomainConfig: () => ({ url: 'https://example.com' }),
}));

vi.mock('utils/cart/useCurrentCart', () => ({
    useCurrentCart: useCurrentCartMock,
}));

vi.mock('utils/cart/useRemoveFromCart', () => ({
    useRemoveFromCart: () => ({ isRemovingFromCart: false, removeFromCart }),
}));

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({ t: (key: string) => key }),
}));

vi.mock('utils/staticUrls/getInternationalizedStaticUrls', () => ({
    getInternationalizedStaticUrls: () => ['/cart'],
}));

vi.mock('utils/useFocusTrap', () => ({ useFocusTrap }));

const productItem = (uuid: string, fullName: string, gifts: { uuid: string }[] = []) => ({
    uuid,
    type: TypeCartItemTypeEnum.Product,
    product: { uuid, fullName, gifts },
});

describe('CartInHeaderList', () => {
    beforeEach(() => {
        useCurrentCartMock.mockReturnValue({
            cart: { items: [productItem('cart-item', 'Cart item')], removedGifts: [] },
        });
        removeFromCart.mockClear();
    });
    beforeAll(() => {
        global.ResizeObserver = class ResizeObserver {
            observe() {}
            unobserve() {}
            disconnect() {}
        };
    });

    afterEach(() => {
        cleanup();
        useFocusTrap.mockClear();
    });

    test('keeps the desktop popover capped and focus-trapped', () => {
        render(<CartInHeaderList />);
        const scrollContainer = screen.getByRole('list').parentElement;

        expect(scrollContainer).toHaveClass('max-h-[50dvh]');
        expect(useFocusTrap.mock.calls[0]?.[0]).toHaveProperty('current');
    });

    test('fills a drawer while leaving focus trapping to the drawer', () => {
        render(<CartInHeaderList hideFocusTrap isDrawer />);
        const scrollContainer = screen.getByRole('list').parentElement;

        expect(scrollContainer).toHaveClass('min-h-0', 'flex-1');
        expect(scrollContainer?.parentElement).toHaveClass('flex', 'min-h-0', 'flex-1', 'flex-col');
        expect(useFocusTrap).toHaveBeenCalledWith(undefined);
    });
    test('groups a shared gift once and preserves the original removal index of each product', () => {
        const gift = {
            ...productItem('gift-item', 'Shared gift'),
            type: TypeCartItemTypeEnum.ProductGift,
            product: { uuid: 'gift', fullName: 'Shared gift', gifts: [] },
        };
        const first = productItem('first', 'First product', [{ uuid: 'gift' }, { uuid: 'removed' }]);
        const second = productItem('second', 'Second product', [{ uuid: 'gift' }]);
        useCurrentCartMock.mockReturnValue({
            cart: {
                items: [gift, first, second],
                removedGifts: [{ product: { uuid: 'removed', fullName: 'Declined gift' } }],
            },
        });

        render(<CartInHeaderList />);
        fireEvent.click(screen.getByRole('button', { name: 'Remove Second product' }));

        expect(screen.getAllByRole('listitem')).toHaveLength(2);
        expect(screen.getByText('Shared gift: First product, Second product')).toBeInTheDocument();
        expect(screen.queryByText('Declined gift')).not.toBeInTheDocument();
        expect(removeFromCart).toHaveBeenCalledWith(second, 2);
    });

    test('keeps a gift with no matching parent visible as a standalone item', () => {
        const gift = { ...productItem('gift', 'Unmatched gift'), type: TypeCartItemTypeEnum.ProductGift };
        useCurrentCartMock.mockReturnValue({ cart: { items: [gift] } });

        render(<CartInHeaderList />);

        expect(screen.getByText('Unmatched gift')).toBeInTheDocument();
    });
});
