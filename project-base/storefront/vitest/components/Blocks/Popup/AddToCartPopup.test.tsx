import { render, screen } from '@testing-library/react';
import { AddToCartPopup } from 'components/Blocks/Popup/AddToCartPopup';
import type React from 'react';
import { describe, expect, test, vi } from 'vitest';

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

vi.mock('components/Blocks/Product/ProductGift', () => ({
    ProductGift: () => null,
}));

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
    useFormatPrice: () => (price: string) => price,
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

describe('AddToCartPopup', () => {
    test('uses the product image fallback alt text', () => {
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

        render(<AddToCartPopup addedCartItem={addedCartItem} />);

        expect(screen.getByRole('img')).toHaveAccessibleName('TV, audio - 32" Philips TV');
    });
});
