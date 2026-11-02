import { render, screen, within } from '@testing-library/react';
import { AddToCartPopup } from 'components/Blocks/Popup/AddToCartPopup';
import { TypeCartItemTypeEnum } from 'graphql/types';
import type React from 'react';
import { beforeEach, describe, expect, test, vi } from 'vitest';

vi.mock('components/Basic/ExtendedNextLink/ExtendedNextLink', () => ({
    ExtendedNextLink: ({ children, href }: { children: React.ReactNode; href: string }) => (
        <a href={href}>{children}</a>
    ),
}));

vi.mock('components/Basic/Image/Image', () => ({
    Image: ({ alt }: { alt: string }) => <span aria-label={alt} role="img" />,
}));

vi.mock('components/Blocks/Product/AdditionalServices/AdditionalServices', () => ({
    AdditionalServices: () => null,
}));

vi.mock('components/Blocks/Product/DeferredRecommendedProducts', () => ({
    DeferredRecommendedProducts: () => null,
}));

const { useCurrentCartMock } = vi.hoisted(() => ({ useCurrentCartMock: vi.fn() }));
vi.mock('utils/cart/useCurrentCart', () => ({ useCurrentCart: useCurrentCartMock }));

vi.mock('components/Forms/Button/Button', () => ({
    Button: ({ children }: { children: React.ReactNode }) => <button type="button">{children}</button>,
}));

vi.mock('components/Forms/Button/LinkButton', () => ({
    LinkButton: ({ children, href }: { children: React.ReactNode; href: string }) => <a href={href}>{children}</a>,
}));

vi.mock('components/Layout/Popup/Popup', () => ({
    Popup: ({ children }: { children: React.ReactNode }) => <div>{children}</div>,
}));

vi.mock('components/Layout/VerticalStack/VerticalStack', () => ({
    VerticalStack: ({ children }: { children: React.ReactNode }) => <div>{children}</div>,
}));

vi.mock('components/Pages/Cart/CartItemPrice', () => ({
    CartItemPrice: () => null,
}));

vi.mock('components/providers/DomainConfigProvider', () => ({
    useDomainConfig: () => ({ isLuigisBoxActive: false, url: 'https://example.com' }),
}));

vi.mock('store/useSessionStore', () => ({
    useSessionStore: () => vi.fn(),
}));

vi.mock('utils/cart/useProductAdditionalServices', () => ({
    useProductAdditionalServices: () => ({
        cartItemQuantity: 1,
        isSettingAdditionalServices: false,
        onToggleService: vi.fn(),
        selectedServiceUuids: [],
    }),
}));

vi.mock('utils/formatting/useFormatPrice', () => ({
    useFormatPrice: () => (price: string | number) => (Number(price) === 0 ? 'Free' : String(price)),
}));

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({
        t: (key: string, options?: Record<string, string>) =>
            Object.entries(options ?? {}).reduce(
                (translatedKey, [optionKey, optionValue]) =>
                    translatedKey.replaceAll(`{{ ${optionKey} }}`, optionValue),
                key,
            ),
    }),
}));

vi.mock('utils/staticUrls/getInternationalizedStaticUrls', () => ({
    getInternationalizedStaticUrls: () => ['/cart'],
}));

const addedCartItem = {
    product: {
        __typename: 'RegularProduct',
        additionalServices: [],
        catalogNumber: 'ABC123',
        fullName: '32" Philips TV',
        gifts: [],
        mainCategory: { name: 'TV, audio' },
        mainImage: { name: null, url: '/image.jpg' },
        price: { priceWithVat: '10' },
        slug: '/32-philips-tv',
        unit: { name: 'pcs' },
        uuid: 'product-uuid',
    },
    quantity: 1,
} as any;

const giftItem = {
    uuid: 'gift-item',
    type: TypeCartItemTypeEnum.ProductGift,
    quantity: 3,
    product: { ...addedCartItem.product, uuid: 'gift', fullName: 'Shared gift', giftPrice: { priceWithVat: '2' } },
};

const mockCart = (items: unknown[]) => {
    useCurrentCartMock.mockReturnValue({ cart: { items } });
};

describe('AddToCartPopup', () => {
    beforeEach(() => mockCart([]));

    test('uses the product image fallback alt text', () => {
        render(<AddToCartPopup addedCartItem={addedCartItem} />);

        expect(screen.getByRole('img')).toHaveAccessibleName('TV, audio - 32" Philips TV');
    });
    test('shows only active gifts inside the product card, once and with their cart quantity and prices', () => {
        const product = { ...addedCartItem.product, gifts: [{ uuid: 'gift' }, { uuid: 'declined' }] };
        mockCart([
            { ...addedCartItem, type: TypeCartItemTypeEnum.Product, product },
            {
                ...addedCartItem,
                type: TypeCartItemTypeEnum.Product,
                product: { ...product, uuid: 'other', fullName: 'Other product' },
            },
            giftItem,
            {
                ...giftItem,
                uuid: 'unrelated-item',
                product: { ...giftItem.product, uuid: 'unrelated', fullName: 'Unrelated gift' },
            },
        ]);

        render(<AddToCartPopup addedCartItem={{ ...addedCartItem, product }} />);

        const productCard = screen.getByRole('region', { name: product.fullName });
        const gift = within(productCard).getByRole('listitem');
        expect(within(gift).getByText('Shared gift')).toBeInTheDocument();
        expect(screen.getAllByText('Shared gift')).toHaveLength(1);
        expect(within(gift).getByText('3 pcs')).toBeInTheDocument();
        expect(within(gift).getByText('2')).toBeInTheDocument();
        expect(within(gift).getByText('6')).toBeInTheDocument();
        expect(within(gift).getByText('Gift for: 32" Philips TV, Other product')).toBeInTheDocument();
        expect(screen.queryByText('Unrelated gift')).not.toBeInTheDocument();
        expect(screen.queryByRole('checkbox')).not.toBeInTheDocument();
    });

    test('does not advertise an offered gift that was removed from the cart', () => {
        render(
            <AddToCartPopup
                addedCartItem={{ ...addedCartItem, product: { ...addedCartItem.product, gifts: [{ uuid: 'gift' }] } }}
            />,
        );

        expect(screen.queryByText('Gift with purchase')).not.toBeInTheDocument();
    });

    test('shows Free only once for a free gift', () => {
        mockCart([{ ...giftItem, product: { ...giftItem.product, giftPrice: { priceWithVat: '0' } } }]);

        render(
            <AddToCartPopup
                addedCartItem={{ ...addedCartItem, product: { ...addedCartItem.product, gifts: [{ uuid: 'gift' }] } }}
            />,
        );

        const gift = screen.getByRole('listitem');
        expect(within(gift).getAllByText('Free')).toHaveLength(1);
        expect(within(gift).queryByText('/ pcs')).not.toBeInTheDocument();
    });
});
