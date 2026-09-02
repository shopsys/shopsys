import { fireEvent, screen } from '@testing-library/react';
import { ProductReviewsActions } from 'components/Blocks/ProductReviews/ProductReviewsActions';
import { DomainConfigProvider } from 'components/providers/DomainConfigProvider';
import { type ComponentPropsWithoutRef, type ReactNode } from 'react';
import { useSessionStore } from 'store/useSessionStore';
import { beforeEach, describe, expect, test, vi } from 'vitest';
import { defaultTestDomainConfig } from 'vitest/helpers/mockPublicConfig';
import { renderWithTooltipProvider as render } from 'vitest/helpers/renderWithTooltipProvider';

type NextLinkMockProps = ComponentPropsWithoutRef<'a'> & {
    prefetch?: boolean;
};

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({
        t: (key: string) => key,
    }),
}));

vi.mock('components/Basic/Link/Link', () => ({
    Link: ({ children, href }: { children: ReactNode; href: string }) => <a href={href}>{children}</a>,
    linkPlaceholderTwClass: 'link-placeholder',
}));

vi.mock('next/link', () => ({
    default: ({ children, href, onClick, prefetch: _prefetch, ...props }: NextLinkMockProps) => (
        <a
            {...props}
            href={href}
            onClick={(event) => {
                event.preventDefault();
                onClick?.(event);
            }}
        >
            {children}
        </a>
    ),
}));

describe('ProductReviewsActions', () => {
    beforeEach(() => {
        useSessionStore.setState({ isPageLoading: false, redirectPageType: undefined });
    });

    test('explains why another review cannot be written', () => {
        render(
            <DomainConfigProvider domainConfig={defaultTestDomainConfig}>
                <ProductReviewsActions
                    canWriteReview={false}
                    hasAlreadyReviewed
                    reviewedProductReviewUrl={null}
                    policyArticleUrl={null}
                    onWriteReview={vi.fn()}
                />
            </DomainConfigProvider>,
        );

        expect(screen.getByText('You have already reviewed this product.')).toBeInTheDocument();
        expect(screen.queryByRole('button', { name: 'Write a review' })).not.toBeInTheDocument();
    });

    test('links an already reviewed product to the specific customer review', () => {
        render(
            <DomainConfigProvider domainConfig={defaultTestDomainConfig}>
                <ProductReviewsActions
                    canWriteReview={false}
                    hasAlreadyReviewed
                    policyArticleUrl={null}
                    reviewedProductReviewUrl="/customer/my-reviews#product-review-review-uuid"
                    onWriteReview={vi.fn()}
                />
            </DomainConfigProvider>,
        );

        const reviewLink = screen.getByRole('link', { name: 'You have already reviewed this product.' });

        expect(reviewLink).toHaveAttribute('href', '/customer/my-reviews#product-review-review-uuid');

        fireEvent.click(reviewLink);

        expect(useSessionStore.getState()).toMatchObject({
            isPageLoading: true,
            redirectPageType: 'myReviews',
        });
    });
});
