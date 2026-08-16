import { render, screen, within } from '@testing-library/react';
import { PRODUCT_COMPARISON_STICKY_TRIGGER_ID } from 'components/Pages/ProductComparison/ProductComparisonHead';
import { ProductComparisonHeadItem } from 'components/Pages/ProductComparison/ProductComparisonHeadItem';
import { TypeProductInProductListFragment } from 'graphql/requests/productLists/fragments/ProductInProductListFragment.generated';
import type React from 'react';
import { describe, expect, test, vi } from 'vitest';

vi.mock('components/Basic/ExtendedNextLink/ExtendedNextLink', () => ({
    ExtendedNextLink: ({
        children,
        className,
        href,
        'aria-label': ariaLabel,
    }: {
        children: React.ReactNode;
        className?: string;
        href: string;
        'aria-label'?: string;
    }) => (
        <a aria-label={ariaLabel} className={className} href={href}>
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
    useDomainConfig: () => ({ defaultLocale: 'en', url: 'https://example.com' }),
}));

vi.mock('graphql/requests/settings/queries/SettingsQuery.generated', () => ({
    useSettingsQuery: () => [
        {
            data: {
                settings: {
                    productReviewsEnabled: true,
                    productReviewMinimalAverageRatingForListing: null,
                },
            },
        },
    ],
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
        __typename: 'RegularProduct',
        catalogNumber: 'ABC123',
        fullName: '32" Philips TV',
        isMainVariant: false,
        mainImage: { url: '/image.jpg' },
        price: { percentageDiscount: 0 },
        reviewsSummary: { averageRating: 4.5, totalCount: 2 },
        slug: '/32-philips-tv',
        uuid: 'product-uuid',
    } as TypeProductInProductListFragment;

    test('shows the product rating outside the image and product name link', () => {
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

        const productLink = screen.getByRole('link', { name: 'Go to product page of 32" Philips TV' });
        const reviewsLink = screen.getByRole('link', { name: 'Average rating 4.5 out of 5, go to reviews' });

        expect(reviewsLink).toHaveAttribute('href', '/32-philips-tv#reviews');
        expect(reviewsLink).toHaveClass('row-start-2');
        expect(reviewsLink).toHaveTextContent('4.5');
        expect(reviewsLink).toHaveTextContent('2 reviews');
        expect(productLink).not.toContainElement(reviewsLink);
        expect(productLink.parentElement).toBe(reviewsLink.parentElement);
        const productImage = within(productLink).getByRole('img');
        const productName = within(productLink).getByText('32" Philips TV');

        expect(productImage).toBeInTheDocument();
        expect(productName).toBeInTheDocument();
        expect(productName).toHaveClass('row-start-3');
        expect(productName).not.toHaveClass('text-link-default', 'underline');
        expect(productName).not.toHaveClass('group-hover/product-link:text-link-hovered');
        expect(productName).toHaveClass('group-hover/product-link:underline');
        expect(within(productLink).queryByText('Code: ABC123')).not.toBeInTheDocument();
        expect(within(productLink).queryByText('4.5')).not.toBeInTheDocument();
        expect(productName).toHaveAttribute('id', PRODUCT_COMPARISON_STICKY_TRIGGER_ID);
    });
});
