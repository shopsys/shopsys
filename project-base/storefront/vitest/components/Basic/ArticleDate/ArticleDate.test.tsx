import { render } from '@testing-library/react';
import { ArticleDate } from 'components/Basic/ArticleDate/ArticleDate';
import { beforeEach, describe, expect, test, vi } from 'vitest';

vi.mock('utils/formatting/useFormatDate', () => ({
    useFormatDate: () => ({
        formatDate: (date: string) => `formatted-${date}`,
    }),
}));

const getTimeElement = (container: HTMLElement): HTMLTimeElement => {
    const timeElement = container.querySelector('time');
    if (!timeElement) {
        throw new Error('time element not found');
    }
    return timeElement;
};

describe('ArticleDate', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    describe('renders correctly', () => {
        test('renders a time element', () => {
            const { container } = render(<ArticleDate date="2024-03-15T10:30:00Z" />);

            const timeElement = getTimeElement(container);
            expect(timeElement).toBeInTheDocument();
        });

        test('renders with custom className', () => {
            const { container } = render(<ArticleDate className="custom-class" date="2024-03-15T10:30:00Z" />);

            const timeElement = getTimeElement(container);
            expect(timeElement).toHaveClass('custom-class');
        });

        test('renders with data-tid attribute when provided', () => {
            const { container } = render(<ArticleDate date="2024-03-15T10:30:00Z" tid="test-tid" />);

            const timeElement = getTimeElement(container);
            expect(timeElement).toHaveAttribute('data-tid', 'test-tid');
        });

        test('renders with formatted display date', () => {
            const { container } = render(<ArticleDate date="2024-03-15T10:30:00Z" />);

            const timeElement = getTimeElement(container);
            expect(timeElement).toHaveTextContent('formatted-2024-03-15T10:30:00Z');
        });
    });

    describe('dateTime attribute ISO format', () => {
        test('formats ISO date string to YYYY-MM-DD', () => {
            const { container } = render(<ArticleDate date="2024-03-15T10:30:00Z" />);

            const timeElement = getTimeElement(container);
            expect(timeElement).toHaveAttribute('dateTime', '2024-03-15');
        });

        test('formats date with different timezone to correct UTC date', () => {
            const { container } = render(<ArticleDate date="2024-01-01T02:00:00+05:00" />);

            const timeElement = getTimeElement(container);
            // 2024-01-01T02:00:00+05:00 in UTC is 2023-12-31T21:00:00Z
            expect(timeElement).toHaveAttribute('dateTime', '2023-12-31');
        });

        test('formats date string without time component', () => {
            const { container } = render(<ArticleDate date="2024-06-20" />);

            const timeElement = getTimeElement(container);
            expect(timeElement).toHaveAttribute('dateTime', '2024-06-20');
        });

        test('formats date at midnight correctly', () => {
            const { container } = render(<ArticleDate date="2024-12-25T00:00:00Z" />);

            const timeElement = getTimeElement(container);
            expect(timeElement).toHaveAttribute('dateTime', '2024-12-25');
        });

        test('formats date at end of day correctly', () => {
            const { container } = render(<ArticleDate date="2024-12-25T23:59:59Z" />);

            const timeElement = getTimeElement(container);
            expect(timeElement).toHaveAttribute('dateTime', '2024-12-25');
        });

        test('handles date crossing year boundary due to timezone', () => {
            // Dec 31, 2024 at 11PM Eastern (UTC-5) is Jan 1, 2025 4AM UTC
            const { container } = render(<ArticleDate date="2024-12-31T23:00:00-05:00" />);

            const timeElement = getTimeElement(container);
            expect(timeElement).toHaveAttribute('dateTime', '2025-01-01');
        });

        test('handles date crossing month boundary due to timezone', () => {
            // Jan 31, 2024 at 11PM Eastern (UTC-5) is Feb 1, 2024 4AM UTC
            const { container } = render(<ArticleDate date="2024-01-31T23:00:00-05:00" />);

            const timeElement = getTimeElement(container);
            expect(timeElement).toHaveAttribute('dateTime', '2024-02-01');
        });

        test('formats leap year date correctly', () => {
            const { container } = render(<ArticleDate date="2024-02-29T12:00:00Z" />);

            const timeElement = getTimeElement(container);
            expect(timeElement).toHaveAttribute('dateTime', '2024-02-29');
        });
    });

    describe('default styling', () => {
        test('has default font classes', () => {
            const { container } = render(<ArticleDate date="2024-03-15T10:30:00Z" />);

            const timeElement = getTimeElement(container);
            expect(timeElement).toHaveClass('font-secondary');
            expect(timeElement).toHaveClass('text-sm');
            expect(timeElement).toHaveClass('font-semibold');
        });
    });
});
