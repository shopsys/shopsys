import { act, fireEvent, render } from '@testing-library/react';
import { Spinbox } from 'components/Forms/Spinbox/Spinbox';
import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest';

vi.mock('next-translate/useTranslation', () => ({
    __esModule: true,
    default: () => ({
        t: (key: string) => key,
    }),
}));

describe('Spinbox debounce behavior', () => {
    const defaultProps = {
        min: 1,
        step: 1,
        defaultValue: 1,
        id: 'test-spinbox',
    };

    beforeEach(() => {
        vi.useFakeTimers();
    });

    afterEach(() => {
        vi.useRealTimers();
        vi.clearAllMocks();
    });

    test('reports rapid clicks once with the final value', () => {
        const onChangeCallback = vi.fn();
        const { container } = render(<Spinbox {...defaultProps} onChangeValueCallback={onChangeCallback} />);

        const increaseButton = container.querySelector('button[title="Increase"]') as HTMLButtonElement;

        fireEvent.click(increaseButton);
        fireEvent.click(increaseButton);
        fireEvent.click(increaseButton);
        fireEvent.click(increaseButton);

        expect(onChangeCallback).not.toHaveBeenCalled();

        act(() => {
            vi.advanceTimersByTime(600);
        });

        expect(onChangeCallback).toHaveBeenCalledTimes(1);
        expect(onChangeCallback).toHaveBeenCalledWith(5);
    });

    test('reports returning to the original value after a previous reported change', () => {
        const onChangeCallback = vi.fn();
        const { container } = render(
            <Spinbox {...defaultProps} defaultValue={5} onChangeValueCallback={onChangeCallback} />,
        );

        const decreaseButton = container.querySelector('button[title="Decrease"]') as HTMLButtonElement;
        const increaseButton = container.querySelector('button[title="Increase"]') as HTMLButtonElement;

        fireEvent.click(increaseButton);

        act(() => {
            vi.advanceTimersByTime(600);
        });

        fireEvent.click(decreaseButton);

        act(() => {
            vi.advanceTimersByTime(600);
        });

        expect(onChangeCallback).toHaveBeenCalledTimes(2);
        expect(onChangeCallback).toHaveBeenNthCalledWith(1, 6);
        expect(onChangeCallback).toHaveBeenNthCalledWith(2, 5);
    });

    test('reports the final value after rapid changes even when it returns to the original value', () => {
        const onChangeCallback = vi.fn();
        const { container } = render(
            <Spinbox {...defaultProps} defaultValue={5} onChangeValueCallback={onChangeCallback} />,
        );

        const decreaseButton = container.querySelector('button[title="Decrease"]') as HTMLButtonElement;
        const increaseButton = container.querySelector('button[title="Increase"]') as HTMLButtonElement;

        fireEvent.click(increaseButton);
        fireEvent.click(decreaseButton);

        act(() => {
            vi.advanceTimersByTime(600);
        });

        expect(onChangeCallback).toHaveBeenCalledTimes(1);
        expect(onChangeCallback).toHaveBeenCalledWith(5);
    });

    test('does not report the minimum value after rapid decrease followed by remove', () => {
        const onChangeCallback = vi.fn();
        const onMinValueDecrease = vi.fn();
        const { container } = render(
            <Spinbox
                {...defaultProps}
                defaultValue={4}
                minValueDecreaseTitle="Remove from cart"
                onChangeValueCallback={onChangeCallback}
                onMinValueDecrease={onMinValueDecrease}
            />,
        );

        const decreaseButton = container.querySelector('button[title="Decrease"]') as HTMLButtonElement;

        fireEvent.click(decreaseButton);
        fireEvent.click(decreaseButton);
        fireEvent.click(decreaseButton);
        fireEvent.click(decreaseButton);

        act(() => {
            vi.advanceTimersByTime(600);
        });

        expect(onMinValueDecrease).toHaveBeenCalledTimes(1);
        expect(onChangeCallback).not.toHaveBeenCalled();
    });
});
