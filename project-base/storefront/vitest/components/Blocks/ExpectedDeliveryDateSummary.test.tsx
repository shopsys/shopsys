import { render, screen } from '@testing-library/react';
import { ExpectedDeliveryDateSummary } from 'components/Blocks/ExpectedDeliveryDateInfo/ExpectedDeliveryDateSummary';
import { describe, expect, test, vi } from 'vitest';

vi.mock('utils/formatting/useFormatDate', () => ({
    useFormatDate: () => ({ formatDate: () => '10. 9. 2026' }),
}));

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({ t: (key: string) => key }),
}));

describe('ExpectedDeliveryDateSummary', () => {
    test('shows the localized expected delivery date', () => {
        render(<ExpectedDeliveryDateSummary expectedDeliveryDate="2026-09-09T22:00:00+00:00" />);

        expect(screen.getByText('Expected delivery date')).toBeInTheDocument();
        expect(screen.getByText('10. 9. 2026')).toBeInTheDocument();
    });

    test('shows the fallback when no delivery date could be promised', () => {
        render(<ExpectedDeliveryDateSummary expectedDeliveryDate={null} />);

        expect(screen.getByText('The delivery date cannot be determined')).toBeInTheDocument();
    });

    test('does not render before the delivery date is available', () => {
        const { container } = render(<ExpectedDeliveryDateSummary expectedDeliveryDate={undefined} />);

        expect(container).toBeEmptyDOMElement();
    });
});
