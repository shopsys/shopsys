import { render, screen } from '@testing-library/react';
import { ReviewStatus } from 'components/Blocks/ProductReviews/ReviewStatus';
import { TypeProductReviewStatusEnum } from 'graphql/types';
import { describe, expect, test, vi } from 'vitest';

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({ t: (key: string) => key }),
}));

describe('ReviewStatus', () => {
    test.each([
        [TypeProductReviewStatusEnum.Pending, 'Under review', 'bg-status-badge-bg-warning'],
        [TypeProductReviewStatusEnum.Approved, 'Published', 'bg-status-badge-bg-success'],
        [TypeProductReviewStatusEnum.Rejected, 'Not published', 'bg-status-badge-bg-error'],
    ] as const)('renders %s as the corresponding status badge', (status, label, expectedClass) => {
        render(<ReviewStatus status={status} />);

        expect(screen.getByText(label)).toHaveClass(expectedClass);
    });
});
