import { render, screen } from '@testing-library/react';
import { ProductListReviewsSummaryLink } from 'components/Blocks/ProductReviews/ProductListReviewsSummaryLink';
import type { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import type { ReactNode } from 'react';
import { describe, expect, test, vi } from 'vitest';

vi.mock('components/Basic/ExtendedNextLink/ExtendedNextLink', () => ({
    ExtendedNextLink: ({ children, className, href }: { children: ReactNode; className?: string; href: string }) => (
        <a className={className} href={href}>
            {children}
        </a>
    ),
}));

vi.mock('components/providers/DomainConfigProvider', () => ({
    useDomainConfig: () => ({ defaultLocale: 'en' }),
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
        t: (key: string, options?: { averageRating?: string; count?: number }) =>
            key
                .replace('{{ averageRating }}', options?.averageRating ?? '')
                .replace('{{ count }}', String(options?.count ?? '')),
    }),
}));

const variantProduct = {
    __typename: 'Variant',
    isMainVariant: false,
    reviewsSummary: null,
    slug: '/reviewed-variant',
    mainVariant: {
        __typename: 'MainVariant',
        reviewsSummary: {
            __typename: 'ProductReviewsSummary',
            averageRating: 4.5,
            totalCount: 2,
        },
    },
} as TypeListedProductFragment;

describe('ProductListReviewsSummaryLink', () => {
    test('shows the product family reviews summary for a variant tile', () => {
        render(<ProductListReviewsSummaryLink product={variantProduct} />);

        expect(screen.getByText('4.5')).toBeInTheDocument();
        expect(screen.getByText('2 reviews')).toBeInTheDocument();
        expect(screen.getByRole('link')).toHaveAttribute('href', '/reviewed-variant#reviews');
    });

    test('wraps the review count on mobile when requested', () => {
        render(<ProductListReviewsSummaryLink isReviewCountWrappedOnMobile product={variantProduct} />);

        expect(screen.getByText('2 reviews')).toBeVisible();
        expect(screen.getByRole('link')).toHaveClass('flex-col', 'items-start', 'sm:flex-row', 'sm:items-center');
    });

    test('uses the provided product URL for the reviews link', () => {
        render(<ProductListReviewsSummaryLink product={variantProduct} productUrl="/main-variant" />);

        expect(screen.getByRole('link')).toHaveAttribute('href', '/main-variant#reviews');
    });
});
