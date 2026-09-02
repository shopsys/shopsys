import { screen } from '@testing-library/react';
import { MyReviewItem } from 'components/Pages/Customer/MyReviews/MyReviewItem';
import { TypeCustomerUserProductReviewFragment } from 'graphql/requests/productReviews/fragments/CustomerUserProductReviewFragment.generated';
import { TypeProductReviewStatusEnum } from 'graphql/types';
import { type ReactNode } from 'react';
import { describe, expect, test, vi } from 'vitest';
import { renderWithTooltipProvider as render } from 'vitest/helpers/renderWithTooltipProvider';

vi.mock('components/Basic/ExtendedNextLink/ExtendedNextLink', () => ({
    ExtendedNextLink: ({ children, className, href }: { children: ReactNode; className?: string; href: string }) => (
        <a className={className} href={href}>
            {children}
        </a>
    ),
}));

vi.mock('components/Blocks/ProductReviews/ReviewStatus', () => ({
    ReviewStatus: () => <span>Review status</span>,
}));

vi.mock('utils/formatting/useFormatDate', () => ({
    useFormatDate: () => ({ formatDate: () => 'June 5, 2026' }),
}));

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({
        t: (key: string) => (key === 'reviews@shopsys.cz' ? 'reviews@example.com' : key),
    }),
}));

const productReview: TypeCustomerUserProductReviewFragment = {
    __typename: 'ProductReview',
    uuid: 'review-uuid',
    reviewerName: 'John Doe',
    rating: 4,
    text: 'Review text',
    createdAt: '2026-06-05T12:00:00+00:00',
    isVerifiedPurchase: false,
    status: TypeProductReviewStatusEnum.Rejected,
    rejectionReason: 'The review violates the rules.',
    responseText: null,
    responseCreatedAt: null,
    productUuid: 'product-uuid',
    productName: 'Product',
    product: null,
};

describe('MyReviewItem', () => {
    test('shows the review contact after a rejection reason', () => {
        render(<MyReviewItem productReview={productReview} />);

        expect(screen.getByText('Rejection reason')).toBeInTheDocument();
        expect(screen.getByText(productReview.rejectionReason as string)).toBeInTheDocument();
        expect(screen.getByRole('link', { name: 'reviews@example.com' })).toHaveAttribute(
            'href',
            'mailto:reviews@example.com',
        );
    });

    test('does not show the review contact for an approved review', () => {
        render(
            <MyReviewItem
                productReview={{
                    ...productReview,
                    status: TypeProductReviewStatusEnum.Approved,
                }}
            />,
        );

        expect(screen.queryByText('Rejection reason')).not.toBeInTheDocument();
        expect(screen.queryByRole('link', { name: 'reviews@example.com' })).not.toBeInTheDocument();
    });

    test('shows the shop response with its creation date', () => {
        render(
            <MyReviewItem
                productReview={{
                    ...productReview,
                    responseText: 'Thank you for your feedback.',
                    responseCreatedAt: '2026-06-06T12:00:00+00:00',
                }}
            />,
        );

        expect(screen.getByText('Shop response')).toBeInTheDocument();
        expect(screen.getByText('Thank you for your feedback.')).toBeInTheDocument();
        expect(screen.getAllByText('June 5, 2026')).toHaveLength(2);
    });

    test('uses the current full product name and exposes a direct review anchor', () => {
        const { container } = render(
            <MyReviewItem
                productReview={{
                    ...productReview,
                    status: TypeProductReviewStatusEnum.Approved,
                    product: {
                        fullName: 'Prefix Product Suffix',
                        isVisible: false,
                        mainImage: {
                            url: '/product.webp',
                        },
                        slug: '/product',
                    },
                }}
            />,
        );

        expect(screen.queryByText('John Doe')).not.toBeInTheDocument();
        expect(screen.getByText('Prefix Product Suffix')).toBeInTheDocument();
        expect(screen.getByAltText('Prefix Product Suffix')).toBeInTheDocument();
        expect(screen.getByText('Review status').parentElement).toHaveClass('ml-auto');
        expect(container.firstElementChild?.tagName).toBe('LI');
        expect(container.firstElementChild).toHaveAttribute('id', 'product-review-review-uuid');
    });

    test('wraps a visible product image and name in one link with hover styles only on the name', () => {
        const { container } = render(
            <MyReviewItem
                productReview={{
                    ...productReview,
                    status: TypeProductReviewStatusEnum.Approved,
                    product: {
                        fullName: 'Linked Product',
                        isVisible: true,
                        mainImage: null,
                        slug: '/product',
                    },
                }}
            />,
        );

        const productLink = screen.getByText('Linked Product').closest('a');

        expect(productLink).toContainElement(screen.getByAltText('Linked Product'));
        expect(container.querySelectorAll('a[href="/product"]')).toHaveLength(1);
        expect(productLink?.querySelector('a')).not.toBeInTheDocument();
        expect(screen.getByText('Linked Product').parentElement).toHaveClass(
            'group-hover/product-link:text-link-hovered',
            'group-hover/product-link:underline',
        );
        expect(screen.getByAltText('Linked Product').parentElement).not.toHaveClass('hover:border-border-less');
    });
});
