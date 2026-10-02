import { render, screen } from '@testing-library/react';
import { CheckmarkIcon } from 'components/Basic/Icon/CheckmarkIcon';
import { StatusBadge, StatusBadgeVariant } from 'components/Basic/StatusBadge/StatusBadge';
import { describe, expect, test } from 'vitest';

const variantClasses: Record<StatusBadgeVariant, string> = {
    error: 'bg-status-badge-bg-error text-status-badge-text-error',
    success: 'bg-status-badge-bg-success text-status-badge-text-success',
    warning: 'bg-status-badge-bg-warning text-status-badge-text-warning',
    neutral: 'bg-background-more text-text-default',
    info: 'bg-status-badge-bg-info text-status-badge-text-info',
};

describe('StatusBadge', () => {
    test.each(Object.entries(variantClasses))('renders the %s variant', (variant, expectedClasses) => {
        render(
            <StatusBadge icon={CheckmarkIcon} variant={variant as StatusBadgeVariant}>
                Status
            </StatusBadge>,
        );

        const badge = screen.getByText('Status');
        expect(badge).toHaveClass(...expectedClasses.split(' '));
        expect(badge.querySelector('svg')).toHaveAttribute('aria-hidden', 'true');
    });
});
