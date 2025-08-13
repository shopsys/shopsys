import { fireEvent, render, cleanup, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { RangeSlider } from 'components/Basic/RangeSlider/RangeSlider';
import { describe, expect, test, vi, beforeEach, afterEach } from 'vitest';

vi.mock('next-translate/useTranslation', () => ({
    __esModule: true,
    default: () => ({
        t: (key: string) => key,
    }),
}));

Object.defineProperty(window, 'getSelection', {
    writable: true,
    value: vi.fn(() => ({
        removeAllRanges: vi.fn(),
    })),
});

describe('RangeSlider Component', () => {
    const defaultProps = {
        min: 0,
        max: 1000,
        minValue: 100,
        maxValue: 800,
        setMinValueCallback: vi.fn(),
        setMaxValueCallback: vi.fn(),
        title: 'Price Filter',
    };

    beforeEach(() => {
        vi.clearAllMocks();
    });

    afterEach(() => {
        cleanup();
    });

    describe('Basic Rendering', () => {
        test('renders range slider with correct structure', () => {
            render(<RangeSlider {...defaultProps} />);

            const minThumb = screen.getByLabelText('Minimum value');
            const maxThumb = screen.getByLabelText('Maximum value');

            expect(minThumb).toBeInTheDocument();
            expect(maxThumb).toBeInTheDocument();
            expect(minThumb).toHaveAttribute('type', 'range');
            expect(maxThumb).toHaveAttribute('type', 'range');
        });

        test('renders text inputs with correct labels and values', () => {
            render(<RangeSlider {...defaultProps} />);

            const minInput = screen.getByLabelText('from');
            const maxInput = screen.getByLabelText('to');

            expect(minInput).toBeInTheDocument();
            expect(maxInput).toBeInTheDocument();
            expect(minInput).toHaveValue(100);
            expect(maxInput).toHaveValue(800);
            expect(minInput).toHaveAttribute('type', 'number');
            expect(maxInput).toHaveAttribute('type', 'number');
        });

        test('renders with correct min/max attributes', () => {
            render(<RangeSlider {...defaultProps} />);

            const minThumb = screen.getByLabelText('Minimum value');
            const maxThumb = screen.getByLabelText('Maximum value');

            expect(minThumb).toHaveAttribute('min', '0');
            expect(minThumb).toHaveAttribute('max', '1000');
            expect(maxThumb).toHaveAttribute('min', '0');
            expect(maxThumb).toHaveAttribute('max', '1000');
        });

        test('renders with correct step calculation for integer values', () => {
            render(<RangeSlider {...defaultProps} />);

            const minThumb = screen.getByLabelText('Minimum value');
            const maxThumb = screen.getByLabelText('Maximum value');

            expect(minThumb).toHaveAttribute('step', '1');
            expect(maxThumb).toHaveAttribute('step', '1');
        });

        test('renders with correct step calculation for decimal values', () => {
            const decimalProps = {
                ...defaultProps,
                min: 0.1,
                max: 99.9,
                minValue: 10.5,
                maxValue: 80.7,
            };

            render(<RangeSlider {...decimalProps} />);

            const minThumb = screen.getByLabelText('Minimum value');
            const maxThumb = screen.getByLabelText('Maximum value');

            expect(minThumb).toHaveAttribute('step', '0.1');
            expect(maxThumb).toHaveAttribute('step', '0.1');
        });

        test('renders disabled state correctly', () => {
            render(<RangeSlider {...defaultProps} isDisabled />);

            const minThumb = screen.getByLabelText('Minimum value');
            const maxThumb = screen.getByLabelText('Maximum value');
            const minInput = screen.getByLabelText('from');
            const maxInput = screen.getByLabelText('to');

            expect(minThumb).toBeDisabled();
            expect(maxThumb).toBeDisabled();
            expect(minInput).toBeDisabled();
            expect(maxInput).toBeDisabled();
        });
    });

    describe('Value Initialization and Updates', () => {
        test('initializes with provided min and max values', () => {
            render(<RangeSlider {...defaultProps} />);

            const minInput = screen.getByLabelText('from');
            const maxInput = screen.getByLabelText('to');

            expect(minInput).toHaveValue(100);
            expect(maxInput).toHaveValue(800);
        });

        test('handles minValue below min constraint', () => {
            const constraintProps = {
                ...defaultProps,
                minValue: -50,
            };

            render(<RangeSlider {...constraintProps} />);

            const minInput = screen.getByLabelText('from');
            expect(minInput).toHaveValue(0);
        });

        test('handles maxValue above max constraint', () => {
            const constraintProps = {
                ...defaultProps,
                maxValue: 1500,
            };

            render(<RangeSlider {...constraintProps} />);

            const maxInput = screen.getByLabelText('to');
            expect(maxInput).toHaveValue(1000);
        });

        test('handles minValue greater than maxValue', () => {
            const crossoverProps = {
                ...defaultProps,
                minValue: 900,
                maxValue: 200,
            };

            render(<RangeSlider {...crossoverProps} />);

            const minInput = screen.getByLabelText('from');
            expect(minInput).toHaveValue(200);
        });

        test('handles maxValue less than minValue', () => {
            const crossoverProps = {
                ...defaultProps,
                minValue: 900,
                maxValue: 200,
            };

            render(<RangeSlider {...crossoverProps} />);

            const maxInput = screen.getByLabelText('to');
            expect(maxInput).toHaveValue(900);
        });
    });

    describe('Text Input Interactions', () => {
        test('handles minimum input change', async () => {
            const user = userEvent.setup();
            render(<RangeSlider {...defaultProps} />);

            const minInput = screen.getByLabelText('from');

            await user.clear(minInput);
            await user.type(minInput, '150');

            expect(minInput).toHaveValue(150);
        });

        test('handles maximum input change', async () => {
            const user = userEvent.setup();
            render(<RangeSlider {...defaultProps} />);

            const maxInput = screen.getByLabelText('to');

            await user.clear(maxInput);
            await user.type(maxInput, '750');

            expect(maxInput).toHaveValue(750);
        });

        test('calls setMinValueCallback on minimum input blur with valid value', async () => {
            const user = userEvent.setup();
            const setMinValueCallback = vi.fn();
            render(<RangeSlider {...defaultProps} setMinValueCallback={setMinValueCallback} />);

            const minInput = screen.getByLabelText('from');

            await user.clear(minInput);
            await user.type(minInput, '200');
            fireEvent.blur(minInput);

            expect(setMinValueCallback).toHaveBeenCalledWith(200);
        });

        test('calls setMaxValueCallback on maximum input blur with valid value', async () => {
            const user = userEvent.setup();
            const setMaxValueCallback = vi.fn();
            render(<RangeSlider {...defaultProps} setMaxValueCallback={setMaxValueCallback} />);

            const maxInput = screen.getByLabelText('to');

            await user.clear(maxInput);
            await user.type(maxInput, '900');
            fireEvent.blur(maxInput);

            expect(setMaxValueCallback).toHaveBeenCalledWith(900);
        });

        test('resets to min value on blur with invalid minimum input', async () => {
            const user = userEvent.setup();
            const setMinValueCallback = vi.fn();
            render(<RangeSlider {...defaultProps} setMinValueCallback={setMinValueCallback} />);

            const minInput = screen.getByLabelText('from');

            await user.clear(minInput);
            await user.type(minInput, 'abc');
            fireEvent.blur(minInput);

            expect(minInput).toHaveValue(0);
            expect(setMinValueCallback).toHaveBeenCalledWith(0);
        });

        test('resets to max value on blur with value above maximum', async () => {
            const user = userEvent.setup();
            const setMaxValueCallback = vi.fn();
            render(<RangeSlider {...defaultProps} setMaxValueCallback={setMaxValueCallback} />);

            const maxInput = screen.getByLabelText('to');

            await user.clear(maxInput);
            await user.type(maxInput, '1500');
            fireEvent.blur(maxInput);

            expect(maxInput).toHaveValue(1000);
            expect(setMaxValueCallback).toHaveBeenCalledWith(1000);
        });

        test('handles NaN input values', async () => {
            const user = userEvent.setup();
            render(<RangeSlider {...defaultProps} />);

            const minInput = screen.getByLabelText('from');

            await user.clear(minInput);
            await user.type(minInput, 'abc');
            fireEvent.blur(minInput);

            expect(minInput).toHaveValue(0);
        });

        test('clears text selection on blur', async () => {
            const user = userEvent.setup();
            const removeAllRanges = vi.fn();
            const mockGetSelection = vi.fn(() => ({ removeAllRanges }) as unknown as Selection);
            Object.defineProperty(window, 'getSelection', {
                writable: true,
                value: mockGetSelection,
            });

            render(<RangeSlider {...defaultProps} />);

            const minInput = screen.getByLabelText('from');

            await user.clear(minInput);
            await user.type(minInput, '200');
            fireEvent.blur(minInput);

            expect(removeAllRanges).toHaveBeenCalled();
        });
    });

    describe('Slider Thumb Interactions', () => {
        test('handles minimum thumb change', () => {
            render(<RangeSlider {...defaultProps} />);

            const minThumb = screen.getByLabelText('Minimum value');

            fireEvent.change(minThumb, { target: { value: '200' } });

            expect(minThumb).toHaveValue('200');
        });

        test('handles maximum thumb change', () => {
            render(<RangeSlider {...defaultProps} />);

            const maxThumb = screen.getByLabelText('Maximum value');

            fireEvent.change(maxThumb, { target: { value: '700' } });

            expect(maxThumb).toHaveValue('700');
        });

        test('prevents minimum thumb from exceeding maximum value', () => {
            render(<RangeSlider {...defaultProps} />);

            const minThumb = screen.getByLabelText('Minimum value');

            fireEvent.change(minThumb, { target: { value: '900' } });

            expect(minThumb).toHaveValue('800');
        });

        test('prevents maximum thumb from going below minimum value', () => {
            render(<RangeSlider {...defaultProps} />);

            const maxThumb = screen.getByLabelText('Maximum value');

            fireEvent.change(maxThumb, { target: { value: '50' } });

            expect(maxThumb).toHaveValue('100');
        });

        test('calls setMinValueCallback on minimum thumb mouse up', () => {
            const setMinValueCallback = vi.fn();
            render(<RangeSlider {...defaultProps} setMinValueCallback={setMinValueCallback} />);

            const minThumb = screen.getByLabelText('Minimum value');

            fireEvent.change(minThumb, { target: { value: '250' } });
            fireEvent.mouseUp(minThumb);

            expect(setMinValueCallback).toHaveBeenCalledWith(250);
        });

        test('calls setMaxValueCallback on maximum thumb mouse up', () => {
            const setMaxValueCallback = vi.fn();
            render(<RangeSlider {...defaultProps} setMaxValueCallback={setMaxValueCallback} />);

            const maxThumb = screen.getByLabelText('Maximum value');

            fireEvent.change(maxThumb, { target: { value: '750' } });
            fireEvent.mouseUp(maxThumb);

            expect(setMaxValueCallback).toHaveBeenCalledWith(750);
        });

        test('calls setMinValueCallback on minimum thumb touch end', () => {
            const setMinValueCallback = vi.fn();
            render(<RangeSlider {...defaultProps} setMinValueCallback={setMinValueCallback} />);

            const minThumb = screen.getByLabelText('Minimum value');

            fireEvent.change(minThumb, { target: { value: '300' } });
            fireEvent.touchEnd(minThumb);

            expect(setMinValueCallback).toHaveBeenCalledWith(300);
        });

        test('calls setMaxValueCallback on maximum thumb touch end', () => {
            const setMaxValueCallback = vi.fn();
            render(<RangeSlider {...defaultProps} setMaxValueCallback={setMaxValueCallback} />);

            const maxThumb = screen.getByLabelText('Maximum value');

            fireEvent.change(maxThumb, { target: { value: '600' } });
            fireEvent.touchEnd(maxThumb);

            expect(setMaxValueCallback).toHaveBeenCalledWith(600);
        });
    });

    describe('Keyboard Interactions', () => {
        test('Enter key blurs minimum input', async () => {
            const user = userEvent.setup();
            render(<RangeSlider {...defaultProps} />);

            const minInput = screen.getByLabelText('from');

            minInput.focus();
            await user.keyboard('{Enter}');

            expect(minInput).not.toHaveFocus();
        });

        test('Enter key blurs maximum input', async () => {
            const user = userEvent.setup();
            render(<RangeSlider {...defaultProps} />);

            const maxInput = screen.getByLabelText('to');

            maxInput.focus();
            await user.keyboard('{Enter}');

            expect(maxInput).not.toHaveFocus();
        });

        test('Enter key triggers callback on minimum input', async () => {
            const user = userEvent.setup();
            const setMinValueCallback = vi.fn();
            render(<RangeSlider {...defaultProps} setMinValueCallback={setMinValueCallback} />);

            const minInput = screen.getByLabelText('from');

            await user.clear(minInput);
            await user.type(minInput, '250');
            await user.keyboard('{Enter}');

            expect(setMinValueCallback).toHaveBeenCalledWith(250);
        });

        test('Enter key triggers callback on maximum input', async () => {
            const user = userEvent.setup();
            const setMaxValueCallback = vi.fn();
            render(<RangeSlider {...defaultProps} setMaxValueCallback={setMaxValueCallback} />);

            const maxInput = screen.getByLabelText('to');

            await user.clear(maxInput);
            await user.type(maxInput, '650');
            await user.keyboard('{Enter}');

            expect(setMaxValueCallback).toHaveBeenCalledWith(650);
        });

        test('Tab navigation between inputs', async () => {
            const user = userEvent.setup();
            render(<RangeSlider {...defaultProps} />);

            const minInput = screen.getByLabelText('from');
            const maxInput = screen.getByLabelText('to');

            minInput.focus();
            expect(document.activeElement).toBe(minInput);

            await user.tab();
            expect(document.activeElement).toBe(maxInput);
        });

        test('Shift+Tab navigation backwards between inputs', async () => {
            const user = userEvent.setup();
            render(<RangeSlider {...defaultProps} />);

            const minInput = screen.getByLabelText('from');
            const maxInput = screen.getByLabelText('to');

            maxInput.focus();
            expect(document.activeElement).toBe(maxInput);

            await user.tab({ shift: true });
            expect(document.activeElement).toBe(minInput);
        });

        test('range thumbs have tabIndex -1 to prevent keyboard focus', () => {
            render(<RangeSlider {...defaultProps} />);

            const minThumb = screen.getByLabelText('Minimum value');
            const maxThumb = screen.getByLabelText('Maximum value');

            expect(minThumb).toHaveAttribute('tabindex', '-1');
            expect(maxThumb).toHaveAttribute('tabindex', '-1');
        });
    });

    describe('Accessibility', () => {
        test('has proper ARIA labels for range thumbs', () => {
            render(<RangeSlider {...defaultProps} />);

            const minThumb = screen.getByLabelText('Minimum value');
            const maxThumb = screen.getByLabelText('Maximum value');

            expect(minThumb).toHaveAttribute('aria-label', 'Minimum value');
            expect(maxThumb).toHaveAttribute('aria-label', 'Maximum value');
        });

        test('has proper ARIA labels for text inputs', () => {
            render(<RangeSlider {...defaultProps} />);

            const minInput = screen.getByLabelText('from');
            const maxInput = screen.getByLabelText('to');

            expect(minInput).toHaveAttribute('id', 'Price Filter - from');
            expect(maxInput).toHaveAttribute('id', 'Price Filter - to');
        });

        test('has proper IDs based on title prop', () => {
            render(<RangeSlider {...defaultProps} />);

            const minInput = screen.getByLabelText('from');
            const maxInput = screen.getByLabelText('to');

            expect(minInput).toHaveAttribute('id', 'Price Filter - from');
            expect(maxInput).toHaveAttribute('id', 'Price Filter - to');
        });

        test('input labels are properly associated', () => {
            render(<RangeSlider {...defaultProps} />);

            expect(screen.getByText('from')).toBeInTheDocument();
            expect(screen.getByText('to')).toBeInTheDocument();
        });

        test('maintains accessibility in disabled state', () => {
            render(<RangeSlider {...defaultProps} isDisabled />);

            const minThumb = screen.getByLabelText('Minimum value');
            const maxThumb = screen.getByLabelText('Maximum value');
            const minInput = screen.getByLabelText('from');
            const maxInput = screen.getByLabelText('to');

            expect(minThumb).toHaveAttribute('aria-label', 'Minimum value');
            expect(maxThumb).toHaveAttribute('aria-label', 'Maximum value');
            expect(minInput).toHaveAttribute('id', 'Price Filter - from');
            expect(maxInput).toHaveAttribute('id', 'Price Filter - to');
        });
    });

    describe('Edge Cases and Error Handling', () => {
        test('handles very large value ranges', () => {
            const largeRangeProps = {
                ...defaultProps,
                min: 0,
                max: 1000000,
                minValue: 50000,
                maxValue: 750000,
            };

            render(<RangeSlider {...largeRangeProps} />);

            const minInput = screen.getByLabelText('from');
            const maxInput = screen.getByLabelText('to');

            expect(minInput).toHaveValue(50000);
            expect(maxInput).toHaveValue(750000);
        });

        test('handles very small decimal ranges', () => {
            const smallRangeProps = {
                ...defaultProps,
                min: 0.001,
                max: 0.999,
                minValue: 0.1,
                maxValue: 0.8,
            };

            render(<RangeSlider {...smallRangeProps} />);

            const minThumb = screen.getByLabelText('Minimum value');
            expect(minThumb).toHaveAttribute('step', '0.001');
        });

        test('handles negative value ranges', () => {
            const negativeRangeProps = {
                ...defaultProps,
                min: -1000,
                max: 0,
                minValue: -800,
                maxValue: -100,
            };

            render(<RangeSlider {...negativeRangeProps} />);

            const minInput = screen.getByLabelText('from');
            const maxInput = screen.getByLabelText('to');

            expect(minInput).toHaveValue(-800);
            expect(maxInput).toHaveValue(-100);
        });

        test('handles equal min and max values', () => {
            const equalProps = {
                ...defaultProps,
                min: 500,
                max: 500,
                minValue: 500,
                maxValue: 500,
            };

            render(<RangeSlider {...equalProps} />);

            const minInput = screen.getByLabelText('from');
            const maxInput = screen.getByLabelText('to');

            expect(minInput).toHaveValue(500);
            expect(maxInput).toHaveValue(500);
        });

        test('handles prop updates correctly', () => {
            const { rerender } = render(<RangeSlider {...defaultProps} />);

            const minInput = screen.getByLabelText('from');
            expect(minInput).toHaveValue(100);

            const updatedProps = {
                ...defaultProps,
                minValue: 300,
                maxValue: 900,
            };

            rerender(<RangeSlider {...updatedProps} />);
            expect(minInput).toHaveValue(300);
        });

        test('handles empty or invalid decimal input gracefully', async () => {
            const user = userEvent.setup();
            render(<RangeSlider {...defaultProps} />);

            const minInput = screen.getByLabelText('from');

            await user.clear(minInput);
            await user.type(minInput, '...');
            fireEvent.blur(minInput);

            expect(minInput).toHaveValue(0);
        });
    });

    describe('Complex User Workflows', () => {
        test('complete price filtering workflow with keyboard', async () => {
            const user = userEvent.setup();
            const setMinValueCallback = vi.fn();
            const setMaxValueCallback = vi.fn();

            render(
                <RangeSlider
                    {...defaultProps}
                    setMaxValueCallback={setMaxValueCallback}
                    setMinValueCallback={setMinValueCallback}
                />,
            );

            const minInput = screen.getByLabelText('from');
            const maxInput = screen.getByLabelText('to');

            minInput.focus();
            await user.clear(minInput);
            await user.type(minInput, '200');
            await user.keyboard('{Enter}');

            expect(setMinValueCallback).toHaveBeenCalledWith(200);

            maxInput.focus();
            await user.clear(maxInput);
            await user.type(maxInput, '700');
            await user.keyboard('{Enter}');

            expect(setMaxValueCallback).toHaveBeenCalledWith(700);
        });

        test('slider drag and text input interaction', async () => {
            const user = userEvent.setup();
            const setMinValueCallback = vi.fn();
            const setMaxValueCallback = vi.fn();

            render(
                <RangeSlider
                    {...defaultProps}
                    setMaxValueCallback={setMaxValueCallback}
                    setMinValueCallback={setMinValueCallback}
                />,
            );

            const minThumb = screen.getByLabelText('Minimum value');
            const maxInput = screen.getByLabelText('to');

            fireEvent.change(minThumb, { target: { value: '300' } });
            fireEvent.mouseUp(minThumb);

            expect(setMinValueCallback).toHaveBeenCalledWith(300);

            await user.clear(maxInput);
            await user.type(maxInput, '600');
            fireEvent.blur(maxInput);

            expect(setMaxValueCallback).toHaveBeenCalledWith(600);
        });

        test('handles rapid value changes without race conditions', async () => {
            const user = userEvent.setup();
            const setMinValueCallback = vi.fn();

            render(<RangeSlider {...defaultProps} setMinValueCallback={setMinValueCallback} />);

            const minInput = screen.getByLabelText('from');

            await user.clear(minInput);
            await user.type(minInput, '100');
            await user.clear(minInput);
            await user.type(minInput, '200');
            await user.clear(minInput);
            await user.type(minInput, '300');
            fireEvent.blur(minInput);

            expect(setMinValueCallback).toHaveBeenCalledWith(300);
        });

        test('error recovery after invalid input', async () => {
            const user = userEvent.setup();
            const setMinValueCallback = vi.fn();

            render(<RangeSlider {...defaultProps} setMinValueCallback={setMinValueCallback} />);

            const minInput = screen.getByLabelText('from');

            await user.clear(minInput);
            await user.type(minInput, 'invalid');
            fireEvent.blur(minInput);

            expect(minInput).toHaveValue(0);
            expect(setMinValueCallback).toHaveBeenCalledWith(0);

            await user.clear(minInput);
            await user.type(minInput, '250');
            fireEvent.blur(minInput);

            expect(setMinValueCallback).toHaveBeenCalledWith(250);
        });
    });
});
