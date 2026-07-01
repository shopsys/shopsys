import { render, screen } from '@testing-library/react';
import { CartListItem } from 'components/Pages/Cart/CartList/CartListItem';
import { TypeCartItemTypeEnum } from 'graphql/types';
import type React from 'react';
import { describe, expect, test, vi } from 'vitest';

vi.mock('components/Basic/ExtendedNextLink/ExtendedNextLink', () => ({
    ExtendedNextLink: ({ children, href }: { children: React.ReactNode; href: string }) => (
        <a href={href}>{children}</a>
    ),
}));

vi.mock('components/Basic/GiftBadge/GiftBadge', () => ({
    GiftBadge: () => <span>Gift</span>,
}));

vi.mock('components/Basic/Icon/RemoveIcon', () => ({
    RemoveIcon: () => null,
}));

vi.mock('components/Basic/Image/Image', () => ({
    Image: ({ alt }: { alt: string }) => <span aria-label={alt} role="img" />,
}));

vi.mock('components/Blocks/Product/ProductAvailability', () => ({
    ProductAvailability: ({ availability }: { availability: { name: string } }) => <span>{availability.name}</span>,
}));

vi.mock('components/Forms/Button/IconButton', () => ({
    IconButton: ({ ariaLabel, onClick }: { ariaLabel: string; onClick: () => void }) => (
        <button aria-label={ariaLabel} type="button" onClick={onClick} />
    ),
}));

vi.mock('components/Forms/Spinbox/Spinbox', () => ({
    Spinbox: ({ ariaLabel }: { ariaLabel: string }) => <input aria-label={ariaLabel} type="number" />,
}));

vi.mock('components/Pages/Cart/CartItemPrice', () => ({
    CartItemPrice: () => <span>Cart item price</span>,
}));

vi.mock('utils/formatting/useFormatPrice', () => ({
    useFormatPrice: () => (price: string | number) => `€${price}`,
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

vi.mock('utils/useDebounce', () => ({
    useDebounce: (value: unknown) => value,
}));

describe('CartListItem', () => {
    test('adds screen reader summary with code, availability, quantity, unit price and totals', () => {
        const item = {
            freeQuantity: 1,
            product: {
                __typename: 'RegularProduct',
                availability: {
                    name: 'In stock',
                },
                availableStoresCount: 1,
                catalogNumber: 'ABC123',
                categories: [{ name: 'TV, audio' }],
                fullName: '32" Philips TV',
                giftPrice: {
                    priceWithVat: '0',
                    priceWithoutVat: '0',
                },
                isAllowedNegativeStock: false,
                isInquiryType: false,
                mainImage: {
                    url: '/image.jpg',
                },
                price: {
                    priceWithVat: '10',
                    priceWithoutVat: '8',
                },
                promotionBuyQuantity: null,
                promotionFreeQuantity: null,
                slug: '/32-philips-tv',
                stockQuantity: 10,
                unit: {
                    name: 'pcs',
                },
                uuid: 'product-uuid',
            },
            quantity: 3,
            type: TypeCartItemTypeEnum.Product,
            uuid: 'cart-item-uuid',
        };

        render(
            <CartListItem
                isRemovingFromCart={false}
                item={item as any}
                listIndex={0}
                onAddToCart={vi.fn()}
                onRemoveFromCart={vi.fn()}
            />,
        );

        expect(screen.getByRole('region', { name: '32" Philips TV' })).toHaveAccessibleDescription(
            'Code: ABC123. Availability: In stock. Quantity: 3 pcs. Unit price with VAT: €10. Item total with VAT: €20. Item total without VAT: €16. 1 pcs is free.',
        );
    });
});
