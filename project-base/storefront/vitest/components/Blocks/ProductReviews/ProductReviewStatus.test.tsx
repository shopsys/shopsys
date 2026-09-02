import { fireEvent, screen } from '@testing-library/react';
import { ProductReviewStatus } from 'components/Blocks/ProductReviews/ProductReviewStatus';
import { describe, expect, test, vi } from 'vitest';
import { renderWithTooltipProvider as render } from 'vitest/helpers/renderWithTooltipProvider';

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({ t: (key: string) => key }),
}));

describe('ProductReviewStatus', () => {
    test('renders a verified purchase as a success badge', () => {
        render(<ProductReviewStatus status="verifiedPurchase" />);

        expect(screen.getByText('Verified purchase')).toHaveClass('bg-status-badge-bg-success');
        expect(screen.queryByRole('button')).not.toBeInTheDocument();
    });

    test('renders a review awaiting approval as a warning badge', () => {
        render(<ProductReviewStatus status="awaitingApproval" />);

        expect(screen.getByText('Under review')).toHaveClass('bg-status-badge-bg-warning');

        fireEvent.focus(screen.getByRole('button', { name: 'Under review' }));

        expect(screen.getByRole('tooltip')).toHaveTextContent(
            'Your review is awaiting approval. Until it is approved, only you can see it.',
        );
    });
});
