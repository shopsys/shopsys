import { render, screen } from '@testing-library/react';
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
    useFormatPrice: () => (price: string | number) => String(price),
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

describe('CartInHeaderListItem', () => {
    test('uses the product image fallback alt text', () => {
        const cartItem = {
            additionalServices: [],
            product: {
                __typename: 'RegularProduct',
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
});
