import { fireEvent, render, cleanup } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { Spinbox } from 'components/Forms/Spinbox/Spinbox';
import { VALIDATION_CONSTANTS } from 'components/Forms/validationConstants';
import { describe, expect, test, vi, beforeEach, afterEach } from 'vitest';

const { maxCartItemQuantity: MAX_CART_ITEM_QUANTITY } = VALIDATION_CONSTANTS;

vi.mock('next-translate/useTranslation', () => ({
    __esModule: true,
    default: () => ({
        t: (key: string) => key,
    }),
}));

vi.mock('utils/useDebounce', () => ({
    useDebounce: (value: any) => value,
}));

describe('Spinbox Component', () => {
    const defaultProps = {
        min: 1,
        step: 1,
        defaultValue: 5,
        id: 'test-spinbox',
    };

    beforeEach(() => {
        vi.clearAllMocks();
    });

    afterEach(() => {
        cleanup();
    });

    describe('Basic Rendering', () => {
        test('renders input element with correct attributes', () => {
            const { container } = render(<Spinbox {...defaultProps} />);

            const input = container.querySelector('input[type="number"]') as HTMLInputElement;
            expect(input).toBeInTheDocument();
            expect(input).toHaveAttribute('min', '1');
            expect(input).toHaveAttribute('max', MAX_CART_ITEM_QUANTITY.toString());
            expect(input).toHaveValue(5);
        });

        test('renders decrease and increase buttons', () => {
            const { container } = render(<Spinbox {...defaultProps} />);

            const decreaseButton = container.querySelector('button[title="Decrease"]');
            const increaseButton = container.querySelector('button[title="Increase"]');

            expect(decreaseButton).toBeInTheDocument();
            expect(increaseButton).toBeInTheDocument();
        });

        test('uses validation constants for max value', () => {
            const { container } = render(<Spinbox {...defaultProps} />);

            const input = container.querySelector('input[type="number"]') as HTMLInputElement;
            expect(input).toHaveAttribute('max', MAX_CART_ITEM_QUANTITY.toString());
        });

        test('renders with different sizes', () => {
            const { container, rerender } = render(<Spinbox {...defaultProps} size="small" />);
            expect(container.querySelector('input[type="number"]')).toBeInTheDocument();

            rerender(<Spinbox {...defaultProps} size="xlarge" />);
            expect(container.querySelector('input[type="number"]')).toBeInTheDocument();
        });
    });

    describe('Button Interactions', () => {
        test('increase button functionality', async () => {
            const user = userEvent.setup();
            const { container } = render(<Spinbox {...defaultProps} />);

            const input = container.querySelector('input[type="number"]') as HTMLInputElement;
            const increaseButton = container.querySelector('button[title="Increase"]') as HTMLButtonElement;

            await user.click(increaseButton);

            expect(input).toHaveValue(6);
        });

        test('decrease button functionality', async () => {
            const user = userEvent.setup();
            const { container } = render(<Spinbox {...defaultProps} />);

            const input = container.querySelector('input[type="number"]') as HTMLInputElement;
            const decreaseButton = container.querySelector('button[title="Decrease"]') as HTMLButtonElement;

            await user.click(decreaseButton);

            expect(input).toHaveValue(4);
        });

        test('does not decrease below minimum', async () => {
            const user = userEvent.setup();
            const { container } = render(<Spinbox {...defaultProps} defaultValue={1} />);

            const input = container.querySelector('input[type="number"]') as HTMLInputElement;
            const decreaseButton = container.querySelector('button[title="Decrease"]') as HTMLButtonElement;

            await user.click(decreaseButton);

            expect(input).toHaveValue(1);
        });

        test('does not increase above maximum value', async () => {
            const user = userEvent.setup();
            const { container } = render(<Spinbox {...defaultProps} defaultValue={MAX_CART_ITEM_QUANTITY} />);

            const input = container.querySelector('input[type="number"]') as HTMLInputElement;
            const increaseButton = container.querySelector('button[title="Increase"]') as HTMLButtonElement;

            await user.click(increaseButton);

            expect(input).toHaveValue(MAX_CART_ITEM_QUANTITY);
        });

        test('handles continuous button press', async () => {
            const user = userEvent.setup();
            const { container } = render(<Spinbox {...defaultProps} />);

            const input = container.querySelector('input[type="number"]') as HTMLInputElement;
            const increaseButton = container.querySelector('button[title="Increase"]') as HTMLButtonElement;

            await user.click(increaseButton);
            await user.click(increaseButton);
            await user.click(increaseButton);

            expect(input).toHaveValue(8);
        });
    });

    describe('Input Validation', () => {
        test('handles maximum value constraint via typing', async () => {
            const user = userEvent.setup();
            const { container } = render(<Spinbox {...defaultProps} />);

            const input = container.querySelector('input[type="number"]') as HTMLInputElement;

            await user.clear(input);
            await user.type(input, (MAX_CART_ITEM_QUANTITY + 1000).toString());

            expect(input).toHaveValue(MAX_CART_ITEM_QUANTITY);
        });

        test('accepts valid numeric input', async () => {
            const user = userEvent.setup();
            const { container } = render(<Spinbox {...defaultProps} />);

            const input = container.querySelector('input[type="number"]') as HTMLInputElement;

            await user.clear(input);
            await user.type(input, '25');

            expect(input).toHaveValue(25);
        });

        test('handles empty input', async () => {
            const user = userEvent.setup();
            const { container } = render(<Spinbox {...defaultProps} />);

            const input = container.querySelector('input[type="number"]') as HTMLInputElement;

            await user.clear(input);

            expect(input).toHaveValue(null);
        });

        test('restores value on blur when empty', async () => {
            const user = userEvent.setup();
            const onChangeCallback = vi.fn();
            const { container } = render(<Spinbox {...defaultProps} onChangeValueCallback={onChangeCallback} />);

            const input = container.querySelector('input[type="number"]') as HTMLInputElement;

            await user.clear(input);
            fireEvent.blur(input);

            expect(input).toHaveValue(5);
        });

        test('handles NaN and restores to last valid value on blur', async () => {
            const user = userEvent.setup();
            const onChangeCallback = vi.fn();
            const { container } = render(<Spinbox {...defaultProps} onChangeValueCallback={onChangeCallback} />);

            const input = container.querySelector('input[type="number"]') as HTMLInputElement;

            await user.clear(input);
            await user.type(input, '25');
            expect(input).toHaveValue(25);

            await user.keyboard('{Backspace}');
            await user.keyboard('{Backspace}');
            expect(input).toHaveValue(null);

            fireEvent.blur(input);
            expect(input).toHaveValue(25);

            expect(onChangeCallback).toHaveBeenCalledWith(25);
        });

        test('handles legitimate value change from multi-digit to different multi-digit number', async () => {
            const user = userEvent.setup();
            const onChangeCallback = vi.fn();
            const { container } = render(<Spinbox {...defaultProps} onChangeValueCallback={onChangeCallback} />);

            const input = container.querySelector('input[type="number"]') as HTMLInputElement;

            await user.clear(input);
            await user.type(input, '100');
            expect(input).toHaveValue(100);

            await user.keyboard('{Backspace}');
            expect(input).toHaveValue(10);

            await user.keyboard('{Backspace}');
            await user.keyboard('{Backspace}');
            expect(input).toHaveValue(null);

            fireEvent.blur(input);
            expect(input).toHaveValue(10);

            expect(onChangeCallback).toHaveBeenCalledWith(10);
        });

        test('handles legitimate value change from multi-digit to different multi-digit number', async () => {
            const user = userEvent.setup();
            const onChangeCallback = vi.fn();
            const { container } = render(<Spinbox {...defaultProps} onChangeValueCallback={onChangeCallback} />);

            const input = container.querySelector('input[type="number"]') as HTMLInputElement;

            await user.clear(input);
            await user.type(input, '1000');
            expect(input).toHaveValue(1000);

            await user.keyboard('{Backspace}');
            expect(input).toHaveValue(100);

            await user.keyboard('{Backspace}');
            await user.keyboard('{Backspace}');
            await user.keyboard('{Backspace}');
            expect(input).toHaveValue(null);

            fireEvent.blur(input);
            expect(input).toHaveValue(100);

            expect(onChangeCallback).toHaveBeenCalledWith(100);
        });

        test('handles multiple value changes and NaN restoration', async () => {
            const user = userEvent.setup();
            const { container } = render(<Spinbox {...defaultProps} />);

            const input = container.querySelector('input[type="number"]') as HTMLInputElement;

            await user.clear(input);
            await user.type(input, '10');
            expect(input).toHaveValue(10);

            await user.clear(input);
            await user.type(input, '42');
            expect(input).toHaveValue(42);

            await user.clear(input);
            fireEvent.blur(input);
            expect(input).toHaveValue(42);
        });

        test('handles step validation with large values', async () => {
            const user = userEvent.setup();
            const { container } = render(<Spinbox {...defaultProps} step={1000} />);

            const input = container.querySelector('input[type="number"]') as HTMLInputElement;
            const increaseButton = container.querySelector('button[title="Increase"]') as HTMLButtonElement;

            await user.click(increaseButton);

            expect(input).toHaveValue(defaultProps.defaultValue);
        });

        test('rounds decimal values to integers when programmatically set', () => {
            const { container } = render(<Spinbox {...defaultProps} />);

            const input = container.querySelector('input[type="number"]') as HTMLInputElement;

            input.value = '12.7';
            fireEvent.input(input);

            expect(input).toHaveValue(13);
        });
    });

    describe('Callback Functionality', () => {
        test('calls onChange callback when value changes via buttons', () => {
            const onChangeCallback = vi.fn();
            const { container } = render(<Spinbox {...defaultProps} onChangeValueCallback={onChangeCallback} />);

            const increaseButton = container.querySelector('button[title="Increase"]') as HTMLButtonElement;

            fireEvent.click(increaseButton);

            expect(onChangeCallback).toHaveBeenCalledWith(6);
        });

        test('calls callback on blur with restored value', () => {
            const onChangeCallback = vi.fn();
            const { container } = render(<Spinbox {...defaultProps} onChangeValueCallback={onChangeCallback} />);

            const input = container.querySelector('input[type="number"]') as HTMLInputElement;

            fireEvent.change(input, { target: { value: '' } });
            fireEvent.blur(input);

            expect(onChangeCallback).toHaveBeenCalledWith(5);
        });
    });

    describe('Button States', () => {
        test('decrease button disabled at minimum value', () => {
            const { container } = render(<Spinbox {...defaultProps} defaultValue={1} />);

            const decreaseButton = container.querySelector('button[title="Decrease"]') as HTMLButtonElement;
            expect(decreaseButton.getAttribute('tabIndex')).toBe('-1');
        });

        test('increase button disabled at maximum value', () => {
            const { container } = render(<Spinbox {...defaultProps} defaultValue={MAX_CART_ITEM_QUANTITY} />);

            const increaseButton = container.querySelector('button[title="Increase"]') as HTMLButtonElement;
            expect(increaseButton.getAttribute('tabIndex')).toBe('-1');
        });

        test('both buttons enabled at middle values', () => {
            const { container } = render(<Spinbox {...defaultProps} defaultValue={10} />);

            const decreaseButton = container.querySelector('button[title="Decrease"]') as HTMLButtonElement;
            const increaseButton = container.querySelector('button[title="Increase"]') as HTMLButtonElement;

            expect(decreaseButton.getAttribute('tabIndex')).toBe('0');
            expect(increaseButton.getAttribute('tabIndex')).toBe('0');
        });
    });

    describe('Edge Cases', () => {
        test('handles large step values', () => {
            const { container } = render(<Spinbox {...defaultProps} step={100} />);

            const input = container.querySelector('input[type="number"]') as HTMLInputElement;
            const increaseButton = container.querySelector('button[title="Increase"]') as HTMLButtonElement;

            fireEvent.click(increaseButton);

            expect(input).toHaveValue(105);
        });

        test('handles negative minimum values', () => {
            const { container } = render(<Spinbox {...defaultProps} min={-5} />);

            const input = container.querySelector('input[type="number"]') as HTMLInputElement;
            expect(input.getAttribute('min')).toBe('-5');
        });

        test('handles zero step', () => {
            const { container } = render(<Spinbox {...defaultProps} step={0} />);

            const input = container.querySelector('input[type="number"]') as HTMLInputElement;
            expect(input).toBeInTheDocument();
        });
    });

    describe('Accessibility', () => {
        test('has proper aria-label', () => {
            const { container } = render(<Spinbox {...defaultProps} />);

            const input = container.querySelector('input[type="number"]') as HTMLInputElement;
            expect(input.getAttribute('aria-label')).toBe('Quantity');
        });

        test('buttons have proper titles', () => {
            const { container } = render(<Spinbox {...defaultProps} />);

            const decreaseButton = container.querySelector('button[title="Decrease"]') as HTMLButtonElement;
            const increaseButton = container.querySelector('button[title="Increase"]') as HTMLButtonElement;

            expect(decreaseButton.getAttribute('title')).toBe('Decrease');
            expect(increaseButton.getAttribute('title')).toBe('Increase');
        });
    });

    describe('Keyboard Interactions', () => {
        test('can focus and navigate with Tab key', async () => {
            const user = userEvent.setup();
            const { container } = render(<Spinbox {...defaultProps} />);

            const decreaseButton = container.querySelector('button[title="Decrease"]') as HTMLButtonElement;
            const input = container.querySelector('input[type="number"]') as HTMLInputElement;
            const increaseButton = container.querySelector('button[title="Increase"]') as HTMLButtonElement;

            decreaseButton.focus();
            expect(document.activeElement).toBe(decreaseButton);

            await user.tab();
            expect(document.activeElement).toBe(input);

            await user.tab();
            expect(document.activeElement).toBe(increaseButton);
        });

        test('can navigate backwards with Shift+Tab', async () => {
            const user = userEvent.setup();
            const { container } = render(<Spinbox {...defaultProps} />);

            const decreaseButton = container.querySelector('button[title="Decrease"]') as HTMLButtonElement;
            const input = container.querySelector('input[type="number"]') as HTMLInputElement;
            const increaseButton = container.querySelector('button[title="Increase"]') as HTMLButtonElement;

            increaseButton.focus();
            expect(document.activeElement).toBe(increaseButton);

            await user.tab({ shift: true });
            expect(document.activeElement).toBe(input);

            await user.tab({ shift: true });
            expect(document.activeElement).toBe(decreaseButton);
        });

        test('Enter key triggers increase button when focused', async () => {
            const user = userEvent.setup();
            const { container } = render(<Spinbox {...defaultProps} />);

            const input = container.querySelector('input[type="number"]') as HTMLInputElement;
            const increaseButton = container.querySelector('button[title="Increase"]') as HTMLButtonElement;

            increaseButton.focus();
            await user.keyboard('{Enter}');

            expect(input).toHaveValue(6);
        });

        test('Enter key triggers decrease button when focused', async () => {
            const user = userEvent.setup();
            const { container } = render(<Spinbox {...defaultProps} />);

            const input = container.querySelector('input[type="number"]') as HTMLInputElement;
            const decreaseButton = container.querySelector('button[title="Decrease"]') as HTMLButtonElement;

            decreaseButton.focus();
            await user.keyboard('{Enter}');

            expect(input).toHaveValue(4);
        });

        test('Space key triggers increase button when focused', async () => {
            const user = userEvent.setup();
            const { container } = render(<Spinbox {...defaultProps} />);

            const input = container.querySelector('input[type="number"]') as HTMLInputElement;
            const increaseButton = container.querySelector('button[title="Increase"]') as HTMLButtonElement;

            increaseButton.focus();
            await user.keyboard(' ');

            expect(input).toHaveValue(6);
        });

        test('Space key triggers decrease button when focused', async () => {
            const user = userEvent.setup();
            const { container } = render(<Spinbox {...defaultProps} />);

            const input = container.querySelector('input[type="number"]') as HTMLInputElement;
            const decreaseButton = container.querySelector('button[title="Decrease"]') as HTMLButtonElement;

            decreaseButton.focus();
            await user.keyboard(' ');

            expect(input).toHaveValue(4);
        });

        test('prevents decimal point input via keyboard', async () => {
            const user = userEvent.setup();
            const { container } = render(<Spinbox {...defaultProps} />);

            const input = container.querySelector('input[type="number"]') as HTMLInputElement;

            input.focus();
            await user.clear(input);
            await user.type(input, '12.5');

            expect(input.value).toBe('125');
        });

        test('prevents comma decimal separator input via keyboard', async () => {
            const user = userEvent.setup();
            const { container } = render(<Spinbox {...defaultProps} />);

            const input = container.querySelector('input[type="number"]') as HTMLInputElement;

            input.focus();
            await user.clear(input);
            await user.type(input, '12,5');

            expect(input.value).toBe('125');
        });

        test('keyboard increase after attempted decimal input works correctly', async () => {
            const user = userEvent.setup();
            const { container } = render(<Spinbox {...defaultProps} />);

            const input = container.querySelector('input[type="number"]') as HTMLInputElement;
            const increaseButton = container.querySelector('button[title="Increase"]') as HTMLButtonElement;

            input.focus();
            await user.clear(input);
            await user.type(input, '12.5');

            increaseButton.focus();
            await user.keyboard('{Enter}');

            expect(input).toHaveValue(126);
        });

        test('keyboard decrease after attempted decimal input works correctly', async () => {
            const user = userEvent.setup();
            const { container } = render(<Spinbox {...defaultProps} />);

            const input = container.querySelector('input[type="number"]') as HTMLInputElement;
            const decreaseButton = container.querySelector('button[title="Decrease"]') as HTMLButtonElement;

            input.focus();
            await user.clear(input);
            await user.type(input, '12.5');

            decreaseButton.focus();
            await user.keyboard('{Enter}');

            expect(input).toHaveValue(124);
        });

        test('disabled buttons cannot be activated but can be manually focused', async () => {
            const user = userEvent.setup();
            const { container } = render(<Spinbox {...defaultProps} defaultValue={1} />);

            const input = container.querySelector('input[type="number"]') as HTMLInputElement;
            const decreaseButton = container.querySelector('button[title="Decrease"]') as HTMLButtonElement;

            expect(decreaseButton.getAttribute('tabIndex')).toBe('-1');

            decreaseButton.focus();
            expect(document.activeElement).toBe(decreaseButton);

            await user.keyboard('{Enter}');
            expect(input).toHaveValue(1);

            await user.keyboard(' ');
            expect(input).toHaveValue(1);
        });

        test('keyboard navigation respects disabled states', async () => {
            const user = userEvent.setup();
            const { container } = render(<Spinbox {...defaultProps} defaultValue={MAX_CART_ITEM_QUANTITY} />);

            const input = container.querySelector('input[type="number"]') as HTMLInputElement;
            const decreaseButton = container.querySelector('button[title="Decrease"]') as HTMLButtonElement;
            const increaseButton = container.querySelector('button[title="Increase"]') as HTMLButtonElement;

            expect(increaseButton.getAttribute('tabIndex')).toBe('-1');
            expect(decreaseButton.getAttribute('tabIndex')).toBe('0');

            input.focus();
            await user.tab();

            expect(document.activeElement).not.toBe(increaseButton);
        });

        test('Enter key on input focuses next element', async () => {
            const user = userEvent.setup();
            const { container } = render(<Spinbox {...defaultProps} />);

            const input = container.querySelector('input[type="number"]') as HTMLInputElement;

            input.focus();
            await user.keyboard('{Enter}');

            expect(input).toHaveValue(5);
        });

        test('complex keyboard workflow: Tab navigation with Enter activation', async () => {
            const user = userEvent.setup();
            const onChangeCallback = vi.fn();
            const { container } = render(<Spinbox {...defaultProps} onChangeValueCallback={onChangeCallback} />);

            const input = container.querySelector('input[type="number"]') as HTMLInputElement;
            const decreaseButton = container.querySelector('button[title="Decrease"]') as HTMLButtonElement;
            const increaseButton = container.querySelector('button[title="Increase"]') as HTMLButtonElement;

            decreaseButton.focus();
            expect(document.activeElement).toBe(decreaseButton);

            await user.keyboard('{Enter}');
            expect(input).toHaveValue(4);

            await user.tab();
            expect(document.activeElement).toBe(input);

            await user.clear(input);
            await user.type(input, '10');
            expect(input).toHaveValue(10);

            await user.tab();
            expect(document.activeElement).toBe(increaseButton);

            await user.keyboard('{Enter}');
            await user.keyboard('{Enter}');
            expect(input).toHaveValue(12);

            expect(onChangeCallback).toHaveBeenCalledWith(12);
        });

        test('keyboard input handles maximum value constraint', async () => {
            const user = userEvent.setup();
            const { container } = render(<Spinbox {...defaultProps} />);

            const input = container.querySelector('input[type="number"]') as HTMLInputElement;
            const increaseButton = container.querySelector('button[title="Increase"]') as HTMLButtonElement;

            input.focus();
            await user.clear(input);
            await user.type(input, (MAX_CART_ITEM_QUANTITY - 1).toString());

            await user.tab();
            expect(document.activeElement).toBe(increaseButton);
            await user.keyboard('{Enter}');

            expect(input).toHaveValue(MAX_CART_ITEM_QUANTITY);

            await user.keyboard('{Enter}');
            expect(input).toHaveValue(MAX_CART_ITEM_QUANTITY);
        });
    });
});
