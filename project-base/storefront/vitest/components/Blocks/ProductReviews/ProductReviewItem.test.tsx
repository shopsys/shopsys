import { render, screen } from '@testing-library/react';
import { ProductReviewItem } from 'components/Blocks/ProductReviews/ProductReviewItem';
import { TypeProductReviewStatusEnum } from 'graphql/types';
import { describe, expect, test, vi } from 'vitest';

vi.mock('components/Blocks/ProductReviews/ProductReviewStatus', () => ({
    ProductReviewStatus: () => <span>Display status</span>,
}));

vi.mock('components/Blocks/ProductReviews/ReviewStatus', () => ({
    ReviewStatus: () => <span>Moderation status</span>,
}));

vi.mock('utils/formatting/useFormatDate', () => ({
    useFormatDate: () => ({ formatDate: () => 'June 5, 2026' }),
}));

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({ t: (key: string) => key }),
}));

const productReview = {
    reviewerName: 'John Doe',
    rating: 4,
    text: 'Review text',
    createdAt: '2026-06-05T12:00:00+00:00',
};

describe('ProductReviewItem', () => {
    test('renders the product name as variant metadata', () => {
        render(<ProductReviewItem productName="Product" productReview={productReview} status="verifiedPurchase" />);

        const productVariant = screen.getByText(
            (_content, element) => element?.tagName === 'SPAN' && element.textContent === 'variant Product',
        );

        expect(productVariant).toHaveTextContent('variant Product');
        expect(productVariant).toHaveClass('text-text-less', 'text-xs');
        expect(screen.getByText('June 5, 2026')).not.toHaveTextContent('Product');
        expect(screen.getByText('Review text')).not.toHaveTextContent('Product');
        expect(screen.getByText('Display status')).toBeInTheDocument();
        expect(screen.queryByText('Moderation status')).not.toBeInTheDocument();
    });

    test('renders an explicitly provided moderation status on the right', () => {
        render(<ProductReviewItem productReview={productReview} reviewStatus={TypeProductReviewStatusEnum.Approved} />);

        expect(screen.getByText('Moderation status').parentElement).toHaveClass('ml-auto');
    });

    test('can replace the reviewer avatar and name', () => {
        const { container } = render(
            <ProductReviewItem
                leadingContent={<span>Product image</span>}
                productReview={productReview}
                reviewTitle="Product"
            />,
        );

        expect(screen.getByText('Product')).toBeInTheDocument();
        expect(screen.getByText('Product image')).toBeInTheDocument();
        expect(screen.queryByText('John Doe')).not.toBeInTheDocument();
        expect(container.querySelector('.size-10')).not.toBeInTheDocument();
    });
});
