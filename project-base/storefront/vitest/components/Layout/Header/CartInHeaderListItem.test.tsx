import { render, screen, within } from '@testing-library/react';
import { CartInHeaderListItem } from 'components/Layout/Header/Cart/CartInHeaderListItem';
import { TypeCartItemTypeEnum } from 'graphql/types';
import type React from 'react';
import { describe, expect, test, vi } from 'vitest';

vi.mock('components/Basic/ExtendedNextLink/ExtendedNextLink', () => ({
    ExtendedNextLink: ({ children, href }: { children: React.ReactNode; href: string }) => (
        <a href={href}>{children}</a>
    ),
}));

vi.mock('components/Basic/GiftBadge/GiftBadge', () => ({
    GiftBadge: () => null,
}));

vi.mock('components/Basic/Image/Image', () => ({
    Image: ({ alt }: { alt: string }) => <span aria-label={alt} role="img" />,
}));

vi.mock('components/Blocks/Product/AdditionalServices/AdditionalServiceSummaryList', () => ({
    AdditionalServiceSummaryList: () => null,
}));

vi.mock('components/Blocks/Product/CartItemQuantityControls', () => ({
    CartItemQuantityControls: () => null,
}));

vi.mock('components/Pages/Cart/RemoveCartItemButton', () => ({
    RemoveCartItemButton: () => null,
}));

vi.mock('utils/formatting/useFormatPrice', () => ({
    useFormatPrice: () => (price: string | number) => (Number(price) === 0 ? 'Free' : String(price)),
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

const cartItem = {
    additionalServices: [],
    product: {
        __typename: 'RegularProduct',
        uuid: 'product',
        gifts: [],
        catalogNumber: 'ABC123',
        fullName: '32" Philips TV',
        mainCategory: { name: 'TV, audio' },
        mainImage: { name: null, url: '/image.jpg' },
        price: { priceWithVat: '10' },
        slug: '/32-philips-tv',
        unit: { name: 'pcs' },
    },
    quantity: 1,
    type: TypeCartItemTypeEnum.Product,
    uuid: 'cart-item-uuid',
} as any;

describe('CartInHeaderListItem', () => {
    test('uses the product image fallback alt text', () => {
        render(
            <CartInHeaderListItem
                cartItem={cartItem}
                isRemovingFromCart={false}
                listIndex={0}
                onRemoveFromCart={vi.fn()}
            />,
        );

        expect(screen.getByRole('img')).toHaveAccessibleName('TV, audio - 32" Philips TV');
    });
    test.each(['0', '2', '***'])('shows quantity and total only for a gift with unit price %s', (price) => {
        const gift = {
            ...cartItem,
            uuid: 'gift-item',
            type: TypeCartItemTypeEnum.ProductGift,
            quantity: 3,
            product: {
                ...cartItem.product,
                uuid: 'gift',
                fullName: 'Gift product',
                giftPrice: { priceWithVat: price },
            },
        };

        render(
            <CartInHeaderListItem
                cartItem={cartItem}
                gifts={[
                    {
                        state: 'added',
                        cartItem: gift,
                        listIndex: 1,
                        relatedProducts: [
                            { uuid: 'product', fullName: '32" Philips TV' },
                            { uuid: 'other', fullName: 'Other product' },
                        ],
                    },
                ]}
                isRemovingFromCart={false}
                listIndex={0}
                onRemoveFromCart={vi.fn()}
            />,
        );

        const giftRow = screen.getByText('Gift product').closest('li')!;
        expect(within(giftRow).getByText('Gift with purchase')).toBeInTheDocument();
        expect(within(giftRow).getByText('3 pcs')).toBeInTheDocument();
        expect(within(giftRow).getByText('Gift for: 32" Philips TV, Other product')).toBeInTheDocument();
        expect(within(giftRow).queryByRole('checkbox')).not.toBeInTheDocument();
        expect(within(giftRow).queryByText('/ pcs')).not.toBeInTheDocument();
        if (price === '0') {
            expect(within(giftRow).getAllByText('Free')).toHaveLength(1);
        } else if (price === '***') {
            expect(within(giftRow).queryByText(/Free|NaN|\*\*\*/)).not.toBeInTheDocument();
        } else {
            expect(within(giftRow).queryByText('2')).not.toBeInTheDocument();
            expect(within(giftRow).getByText('6')).toBeInTheDocument();
        }
    });
});
