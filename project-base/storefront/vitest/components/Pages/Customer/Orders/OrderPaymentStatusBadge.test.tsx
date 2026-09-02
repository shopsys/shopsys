import { render, screen } from '@testing-library/react';
import { OrderPaymentStatusBadge } from 'components/Pages/Customer/Orders/OrderPaymentStatusBadge';
import { describe, expect, test, vi } from 'vitest';

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({ t: (key: string) => key }),
}));

describe('OrderPaymentStatusBadge', () => {
    test.each([
        [true, false, 'Paid', 'bg-status-badge-bg-success'],
        [false, true, 'Processing', 'bg-status-badge-bg-warning'],
        [false, false, 'Not paid', 'bg-status-badge-bg-error'],
    ] as const)('renders the payment state as the corresponding status badge', (orderIsPaid, orderHasPaymentInProcess, label, expectedClass) => {
        render(
            <OrderPaymentStatusBadge
                orderHasExternalPayment
                orderHasPaymentInProcess={orderHasPaymentInProcess}
                orderIsPaid={orderIsPaid}
            />,
        );

        expect(screen.getByText(label)).toHaveClass(expectedClass);
    });

    test('does not render a badge for an order without external payment', () => {
        const { container } = render(
            <OrderPaymentStatusBadge
                orderHasExternalPayment={false}
                orderHasPaymentInProcess={false}
                orderIsPaid={false}
            />,
        );

        expect(container).toBeEmptyDOMElement();
    });
});
