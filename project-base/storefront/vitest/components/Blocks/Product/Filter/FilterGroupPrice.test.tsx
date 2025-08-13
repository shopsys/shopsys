import { cleanup, fireEvent, render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { FilterGroupPrice } from 'components/Blocks/Product/Filter/FilterGroupPrice';
import { getPriceRounded } from 'utils/mappers/price';
import { useCurrentFilterQuery } from 'utils/queryParams/useCurrentFilterQuery';
import { useUpdateFilterQuery } from 'utils/queryParams/useUpdateFilterQuery';
import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest';

vi.mock('next-translate/useTranslation', () => ({
    __esModule: true,
    default: () => ({
        t: (key: string) => key,
    }),
}));

vi.mock('framer-motion', async () => {
    const React = await import('react');
    return {
        AnimatePresence: ({ children }: any) => children,
        motion: {
            div: ({ children, ...props }: any) => React.createElement('div', props, children),
        },
        m: {
            div: ({ children, ...props }: any) => React.createElement('div', props, children),
        },
    };
});

vi.mock('utils/mappers/price', () => ({
    getPriceRounded: vi.fn((price: string) => parseFloat(price)),
}));

vi.mock('utils/queryParams/useCurrentFilterQuery', () => ({
    useCurrentFilterQuery: vi.fn(),
}));

vi.mock('utils/queryParams/useUpdateFilterQuery', () => ({
    useUpdateFilterQuery: vi.fn(),
}));

Object.defineProperty(window, 'getSelection', {
    writable: true,
    value: vi.fn(() => ({
        removeAllRanges: vi.fn(),
    })),
});

const mockUpdateFilterPriceMinimumQuery = vi.fn();
const mockUpdateFilterPriceMaximumQuery = vi.fn();

describe('FilterGroupPrice Component', () => {
    const defaultProps = {
        title: 'Test Price',
        initialMinPrice: '0',
        initialMaxPrice: '1000',
        isActive: false,
        ariaLabel: 'Test Price',
    };

    beforeEach(() => {
        vi.clearAllMocks();

        vi.mocked(useCurrentFilterQuery).mockReturnValue({
            minimalPrice: 100,
            maximalPrice: 800,
        } as any);

        vi.mocked(useUpdateFilterQuery).mockReturnValue({
            updateFilterPriceMinimumQuery: mockUpdateFilterPriceMinimumQuery,
            updateFilterPriceMaximumQuery: mockUpdateFilterPriceMaximumQuery,
        } as any);
    });

    afterEach(() => {
        cleanup();
    });

    describe('Basic Rendering', () => {
        test('renders filter group with correct title', () => {
            render(<FilterGroupPrice {...defaultProps} />);

            expect(screen.getByText('Test Price')).toBeInTheDocument();
        });

        test('renders range slider when group is open', () => {
            render(<FilterGroupPrice {...defaultProps} />);

            const minInput = screen.getByLabelText('from');
            const maxInput = screen.getByLabelText('to');

            expect(minInput).toBeInTheDocument();
            expect(maxInput).toBeInTheDocument();
        });

        test('displays current filter values from query', () => {
            render(<FilterGroupPrice {...defaultProps} />);

            const minInput = screen.getByLabelText('from');
            const maxInput = screen.getByLabelText('to');

            expect(minInput).toHaveValue(100);
            expect(maxInput).toHaveValue(800);
        });

        test('displays initial values when no filter is active', () => {
            vi.mocked(useCurrentFilterQuery).mockReturnValue({
                minimalPrice: undefined,
                maximalPrice: undefined,
            } as any);

            render(<FilterGroupPrice {...defaultProps} />);

            const minInput = screen.getByLabelText('from');
            const maxInput = screen.getByLabelText('to');

            expect(minInput).toHaveValue(0);
            expect(maxInput).toHaveValue(1000);
        });

        test('shows active state when isActive prop is true', () => {
            render(<FilterGroupPrice {...defaultProps} isActive />);

            const activeIndicator = document.querySelector('.bg-background-success');
            expect(activeIndicator).toBeInTheDocument();
        });

        test('hides active state when isActive prop is false', () => {
            render(<FilterGroupPrice {...defaultProps} isActive={false} />);

            const activeIndicator = document.querySelector('.bg-background-success');
            expect(activeIndicator).not.toBeInTheDocument();
        });
    });

    describe('Filter Group Toggle', () => {
        test('renders with group open by default', () => {
            render(<FilterGroupPrice {...defaultProps} />);

            const minInput = screen.getByLabelText('from');
            expect(minInput).toBeInTheDocument();
        });

        test('toggles group open/closed when title is clicked', async () => {
            const user = userEvent.setup();
            render(<FilterGroupPrice {...defaultProps} />);

            const toggleButton = screen.getByRole('button');
            const minInput = screen.getByLabelText('from');

            expect(minInput).toBeInTheDocument();

            await user.click(toggleButton);

            expect(minInput).not.toBeInTheDocument();
        });

        test('toggle button has proper accessibility attributes', () => {
            render(<FilterGroupPrice {...defaultProps} />);

            const toggleButton = screen.getByRole('button');
            expect(toggleButton).toHaveAttribute('tabIndex', '0');
            expect(toggleButton).toHaveAttribute('type', 'button');
            expect(toggleButton).toHaveAttribute('aria-label', 'Test Price');
        });
    });

    describe('Price Range Integration', () => {
        test('passes correct props to RangeSlider', () => {
            render(<FilterGroupPrice {...defaultProps} />);

            const minThumb = screen.getByLabelText('Minimum value');
            const maxThumb = screen.getByLabelText('Maximum value');

            expect(minThumb).toHaveAttribute('min', '0');
            expect(minThumb).toHaveAttribute('max', '1000');
            expect(maxThumb).toHaveAttribute('min', '0');
            expect(maxThumb).toHaveAttribute('max', '1000');
        });

        test('calls price minimum query update on minimum value change', async () => {
            const user = userEvent.setup();
            render(<FilterGroupPrice {...defaultProps} />);

            const minInput = screen.getByLabelText('from');

            await user.clear(minInput);
            await user.type(minInput, '200');
            fireEvent.blur(minInput);

            expect(mockUpdateFilterPriceMinimumQuery).toHaveBeenCalledWith(200);
        });

        test('calls price maximum query update on maximum value change', async () => {
            const user = userEvent.setup();
            render(<FilterGroupPrice {...defaultProps} />);

            const maxInput = screen.getByLabelText('to');

            await user.clear(maxInput);
            await user.type(maxInput, '900');
            fireEvent.blur(maxInput);

            expect(mockUpdateFilterPriceMaximumQuery).toHaveBeenCalledWith(900);
        });

        test('resets filter when value equals initial value', async () => {
            const user = userEvent.setup();
            render(<FilterGroupPrice {...defaultProps} />);

            const minInput = screen.getByLabelText('from');

            await user.clear(minInput);
            await user.type(minInput, '0');
            fireEvent.blur(minInput);

            expect(mockUpdateFilterPriceMinimumQuery).toHaveBeenCalledWith(undefined);
        });

        test('resets filter when maximum value equals initial maximum value', async () => {
            const user = userEvent.setup();
            render(<FilterGroupPrice {...defaultProps} />);

            const maxInput = screen.getByLabelText('to');

            await user.clear(maxInput);
            await user.type(maxInput, '1000');
            fireEvent.blur(maxInput);

            expect(mockUpdateFilterPriceMaximumQuery).toHaveBeenCalledWith(undefined);
        });

        test('does not update query if value is unchanged', async () => {
            const user = userEvent.setup();
            render(<FilterGroupPrice {...defaultProps} />);

            const minInput = screen.getByLabelText('from');

            await user.clear(minInput);
            await user.type(minInput, '100');
            fireEvent.blur(minInput);

            expect(mockUpdateFilterPriceMinimumQuery).not.toHaveBeenCalled();
        });

        test('does not update query if maximum value is unchanged', async () => {
            const user = userEvent.setup();
            render(<FilterGroupPrice {...defaultProps} />);

            const maxInput = screen.getByLabelText('to');

            await user.clear(maxInput);
            await user.type(maxInput, '800');
            fireEvent.blur(maxInput);

            expect(mockUpdateFilterPriceMaximumQuery).not.toHaveBeenCalled();
        });
    });

    describe('Keyboard Accessibility', () => {
        test('filter group title is keyboard accessible', async () => {
            const user = userEvent.setup();
            render(<FilterGroupPrice {...defaultProps} />);

            const toggleButton = screen.getByRole('button');

            toggleButton.focus();
            expect(document.activeElement).toBe(toggleButton);

            await user.keyboard('{Enter}');
        });

        test('space key activates group toggle', async () => {
            const user = userEvent.setup();
            render(<FilterGroupPrice {...defaultProps} />);

            const toggleButton = screen.getByRole('button');

            toggleButton.focus();
            await user.keyboard(' ');
        });

        test('tab navigation through filter components', async () => {
            const user = userEvent.setup();
            render(<FilterGroupPrice {...defaultProps} />);

            const toggleButton = screen.getByRole('button');
            const minInput = screen.getByLabelText('from');
            const maxInput = screen.getByLabelText('to');

            toggleButton.focus();
            expect(document.activeElement).toBe(toggleButton);

            await user.tab();
            expect(document.activeElement).toBe(minInput);

            await user.tab();
            expect(document.activeElement).toBe(maxInput);
        });

        test('shift+tab navigation backwards', async () => {
            const user = userEvent.setup();
            render(<FilterGroupPrice {...defaultProps} />);

            const toggleButton = screen.getByRole('button');
            const maxInput = screen.getByLabelText('to');
            const minInput = screen.getByLabelText('from');

            maxInput.focus();
            expect(document.activeElement).toBe(maxInput);

            await user.tab({ shift: true });
            expect(document.activeElement).toBe(minInput);

            await user.tab({ shift: true });
            expect(document.activeElement).toBe(toggleButton);
        });

        test('enter key on range inputs triggers filter update', async () => {
            const user = userEvent.setup();
            render(<FilterGroupPrice {...defaultProps} />);

            const minInput = screen.getByLabelText('from');

            await user.clear(minInput);
            await user.type(minInput, '250');
            await user.keyboard('{Enter}');

            expect(mockUpdateFilterPriceMinimumQuery).toHaveBeenCalledWith(250);
        });
    });

    describe('Price Formatting and Validation', () => {
        test('handles decimal price values correctly', () => {
            const decimalProps = {
                ...defaultProps,
                initialMinPrice: '10.50',
                initialMaxPrice: '999.99',
            };

            render(<FilterGroupPrice {...decimalProps} />);

            expect(vi.mocked(getPriceRounded)).toHaveBeenCalledWith('10.50');
            expect(vi.mocked(getPriceRounded)).toHaveBeenCalledWith('999.99');
        });

        test('handles invalid price strings gracefully', () => {
            const invalidProps = {
                ...defaultProps,
                initialMinPrice: 'invalid',
                initialMaxPrice: 'also-invalid',
            };

            expect(() => render(<FilterGroupPrice {...invalidProps} />)).not.toThrow();
        });

        test('handles missing price values', () => {
            const missingProps = {
                ...defaultProps,
                initialMinPrice: '',
                initialMaxPrice: '',
            };

            expect(() => render(<FilterGroupPrice {...missingProps} />)).not.toThrow();
        });
    });

    describe('Integration with Query Parameters', () => {
        test('reflects current filter state from URL', () => {
            const customFilter = {
                minimalPrice: 50,
                maximalPrice: 500,
            };

            vi.mocked(useCurrentFilterQuery).mockReturnValue(customFilter as any);

            render(<FilterGroupPrice {...defaultProps} />);

            const minInput = screen.getByLabelText('from');
            const maxInput = screen.getByLabelText('to');

            expect(minInput).toHaveValue(50);
            expect(maxInput).toHaveValue(500);
        });

        test('handles partial filter state from URL', () => {
            const partialFilter = {
                minimalPrice: 200,
                maximalPrice: undefined,
            };

            vi.mocked(useCurrentFilterQuery).mockReturnValue(partialFilter as any);

            render(<FilterGroupPrice {...defaultProps} />);

            const minInput = screen.getByLabelText('from');
            const maxInput = screen.getByLabelText('to');

            expect(minInput).toHaveValue(200);
            expect(maxInput).toHaveValue(1000);
        });

        test('handles null filter state from URL', () => {
            vi.mocked(useCurrentFilterQuery).mockReturnValue(null as any);

            render(<FilterGroupPrice {...defaultProps} />);

            const minInput = screen.getByLabelText('from');
            const maxInput = screen.getByLabelText('to');

            expect(minInput).toHaveValue(0);
            expect(maxInput).toHaveValue(1000);
        });
    });

    describe('Complex User Workflows', () => {
        test('complete price filtering workflow', async () => {
            const user = userEvent.setup();
            render(<FilterGroupPrice {...defaultProps} />);

            const minInput = screen.getByLabelText('from');
            const maxInput = screen.getByLabelText('to');

            await user.clear(minInput);
            await user.type(minInput, '150');
            fireEvent.blur(minInput);

            expect(mockUpdateFilterPriceMinimumQuery).toHaveBeenCalledWith(150);

            await user.clear(maxInput);
            await user.type(maxInput, '750');
            fireEvent.blur(maxInput);

            expect(mockUpdateFilterPriceMaximumQuery).toHaveBeenCalledWith(750);
        });

        test('filter reset workflow', async () => {
            const user = userEvent.setup();
            render(<FilterGroupPrice {...defaultProps} />);

            const minInput = screen.getByLabelText('from');
            const maxInput = screen.getByLabelText('to');

            await user.clear(minInput);
            await user.type(minInput, '200');
            fireEvent.blur(minInput);

            expect(mockUpdateFilterPriceMinimumQuery).toHaveBeenCalledWith(200);

            await user.clear(minInput);
            await user.type(minInput, '0');
            fireEvent.blur(minInput);

            expect(mockUpdateFilterPriceMinimumQuery).toHaveBeenCalledWith(undefined);

            await user.clear(maxInput);
            await user.type(maxInput, '1000');
            fireEvent.blur(maxInput);

            expect(mockUpdateFilterPriceMaximumQuery).toHaveBeenCalledWith(undefined);
        });

        test('keyboard-only price filtering workflow', async () => {
            const user = userEvent.setup();
            render(<FilterGroupPrice {...defaultProps} />);

            const minInput = screen.getByLabelText('from');
            const maxInput = screen.getByLabelText('to');

            minInput.focus();
            await user.clear(minInput);
            await user.type(minInput, '300');
            await user.keyboard('{Enter}');

            expect(mockUpdateFilterPriceMinimumQuery).toHaveBeenCalledWith(300);

            maxInput.focus();
            await user.clear(maxInput);
            await user.type(maxInput, '750');
            await user.keyboard('{Enter}');

            expect(mockUpdateFilterPriceMaximumQuery).toHaveBeenCalledWith(750);
        });

        test('error recovery from invalid inputs', async () => {
            const user = userEvent.setup();
            render(<FilterGroupPrice {...defaultProps} />);

            const minInput = screen.getByLabelText('from');

            await user.clear(minInput);
            await user.type(minInput, 'invalid');
            fireEvent.blur(minInput);

            expect(mockUpdateFilterPriceMinimumQuery).toHaveBeenCalledWith(undefined);

            expect(minInput).toHaveValue(0);
        });

        test('toggle group while interacting with range slider', async () => {
            const user = userEvent.setup();
            render(<FilterGroupPrice {...defaultProps} />);

            const toggleButton = screen.getByRole('button');
            const minInput = screen.getByLabelText('from');

            await user.clear(minInput);
            await user.type(minInput, '100');

            await user.click(toggleButton);

            await user.click(toggleButton);

            const minInputAfterToggle = screen.getByLabelText('from');
            expect(minInputAfterToggle).toHaveValue(100);
        });
    });

    describe('Component State Management', () => {
        test('maintains group open/closed state independently', async () => {
            const user = userEvent.setup();
            render(<FilterGroupPrice {...defaultProps} />);

            const toggleButton = screen.getByRole('button');

            expect(screen.getByLabelText('from')).toBeInTheDocument();

            await user.click(toggleButton);

            await user.click(toggleButton);

            expect(screen.getByLabelText('from')).toBeInTheDocument();
        });

        test('preserves filter values when group is toggled', async () => {
            const user = userEvent.setup();
            render(<FilterGroupPrice {...defaultProps} />);

            const toggleButton = screen.getByRole('button');
            const minInput = screen.getByLabelText('from');

            await user.clear(minInput);
            await user.type(minInput, '250');
            fireEvent.blur(minInput);

            expect(mockUpdateFilterPriceMinimumQuery).toHaveBeenCalledWith(250);

            await user.click(toggleButton);
            await user.click(toggleButton);

            const minInputAfterToggle = screen.getByLabelText('from');
            expect(minInputAfterToggle).toHaveValue(100);
        });

        test('handles rapid prop updates gracefully', () => {
            const { rerender } = render(<FilterGroupPrice {...defaultProps} />);

            rerender(<FilterGroupPrice {...defaultProps} isActive />);
            rerender(<FilterGroupPrice {...defaultProps} isActive={false} />);
            rerender(<FilterGroupPrice {...defaultProps} initialMaxPrice="2000" initialMinPrice="10" />);

            expect(screen.getByText('Test Price')).toBeInTheDocument();
            expect(screen.getByLabelText('from')).toBeInTheDocument();
        });
    });
});
