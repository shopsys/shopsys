import { render, screen } from '@testing-library/react';
import { ProductReviewsSummaryBadge } from 'components/Blocks/ProductReviews/ProductReviewsSummaryBadge';
import type { TypeProductReviewsSummaryFragment } from 'graphql/requests/productReviews/fragments/ProductReviewsSummaryFragment.generated';
import { describe, expect, test, vi } from 'vitest';

vi.mock('components/providers/DomainConfigProvider', () => ({
    useDomainConfig: () => ({ defaultLocale: 'en' }),
}));

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({
        t: (key: string, options?: { averageRating?: string; count?: number }) =>
            key
                .replace('{{ averageRating }}', options?.averageRating ?? '')
                .replace('{{ count }}', String(options?.count ?? '')),
    }),
}));

const reviewsSummary = {
    __typename: 'ProductReviewsSummary',
    averageRating: 4.5,
    totalCount: 2,
} as TypeProductReviewsSummaryFragment;

describe('ProductReviewsSummaryBadge', () => {
    test('announces the average rating and review count in the reviews link', () => {
        render(<ProductReviewsSummaryBadge reviewsSummary={reviewsSummary} />);

        expect(
            screen.getByRole('link', {
                name: 'Average rating 4.5 out of 5, review count 2, go to reviews',
            }),
        ).toBeInTheDocument();
        expect(screen.getByText('4.5')).toHaveAttribute('aria-hidden', 'true');
    });
});
