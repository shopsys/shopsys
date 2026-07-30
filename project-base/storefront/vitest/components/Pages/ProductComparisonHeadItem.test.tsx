import { render, screen, within } from '@testing-library/react';
import { PRODUCT_COMPARISON_STICKY_TRIGGER_ID } from 'components/Pages/ProductComparison/ProductComparisonHead';
import { ProductComparisonHeadItem } from 'components/Pages/ProductComparison/ProductComparisonHeadItem';
import { TypeProductInProductListFragment } from 'graphql/requests/productLists/fragments/ProductInProductListFragment.generated';
import type React from 'react';
import { describe, expect, test, vi } from 'vitest';

vi.mock('components/Basic/ExtendedNextLink/ExtendedNextLink', () => ({
    ExtendedNextLink: ({
        children,
        href,
        'aria-label': ariaLabel,
    }: {
        children: React.ReactNode;
        href: string;
        'aria-label'?: string;
    }) => (
        <a aria-label={ariaLabel} href={href}>
            {children}
        </a>
    ),
}));

vi.mock('components/Basic/Image/Image', () => ({
    Image: () => <span role="img" />,
}));

vi.mock('components/Blocks/Product/ButtonsAction/ProductCompareButton', () => ({
    ProductCompareButton: () => null,
}));

vi.mock('components/Blocks/Product/ButtonsAction/ProductWishlistButton', () => ({
    ProductWishlistButton: () => null,
}));

vi.mock('components/Blocks/Product/ProductAction', () => ({
    ProductAction: () => null,
}));

vi.mock('components/Blocks/Product/ProductFlags', () => ({
    ProductFlags: () => null,
}));

vi.mock('components/providers/AuthorizationProvider', () => ({
    useAuthorization: () => ({ canSeePrices: true }),
}));

vi.mock('components/providers/DomainConfigProvider', () => ({
    useDomainConfig: () => ({ url: 'https://example.com' }),
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

vi.mock('utils/productLists/comparison/useComparison', () => ({
    useComparison: () => ({ isProductInComparison: () => false }),
}));

vi.mock('utils/productLists/wishlist/useWishlist', () => ({
    useWishlist: () => ({
        isProductInWishlist: () => false,
        toggleProductInWishlist: vi.fn(),
    }),
}));

describe('ProductComparisonHeadItem', () => {
    const product = {
        catalogNumber: 'ABC123',
        fullName: '32" Philips TV',
        mainImage: { url: '/image.jpg' },
        price: { percentageDiscount: 0 },
        slug: '/32-philips-tv',
        uuid: 'product-uuid',
    } as TypeProductInProductListFragment;

    test('uses one product detail link for the image and product name only', () => {
        render(
            <table>
                <tbody>
                    <tr>
                        <ProductComparisonHeadItem
                            listIndex={0}
                            product={product}
                            stickyTriggerId={PRODUCT_COMPARISON_STICKY_TRIGGER_ID}
                            toggleProductInComparison={vi.fn()}
                        />
                    </tr>
                </tbody>
            </table>,
        );

        const productLinks = screen.getAllByRole('link');

        expect(productLinks).toHaveLength(1);
        expect(productLinks[0]).toHaveAccessibleName('Go to product page of 32" Philips TV');
        const productImage = within(productLinks[0]).getByRole('img');
        const productName = within(productLinks[0]).getByText('32" Philips TV');

        expect(productImage).toBeInTheDocument();
        expect(productName).toBeInTheDocument();
        expect(within(productLinks[0]).queryByText('Code: ABC123')).not.toBeInTheDocument();
        expect(productName).toHaveAttribute('id', PRODUCT_COMPARISON_STICKY_TRIGGER_ID);
    });
});
