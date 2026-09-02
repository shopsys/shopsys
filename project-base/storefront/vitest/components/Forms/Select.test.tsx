import { fireEvent, screen } from '@testing-library/react';
import { Select } from 'components/Forms/Select/Select';
import { describe, expect, test, vi } from 'vitest';
import { renderWithTooltipProvider as render } from 'vitest/helpers/renderWithTooltipProvider';

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({
        t: (key: string) => key,
    }),
}));

const oceanOption = { label: 'Ocean', value: 'ocean' };

describe('Select reset action', () => {
    test('shows an accessible tooltip and resets the active option', () => {
        const onResetSelect = vi.fn();

        render(
            <Select
                activeOption={oceanOption}
                ariaLabel="Choose color"
                options={[oceanOption]}
                tid="color-select"
                onResetSelect={onResetSelect}
                onSelectOption={vi.fn()}
            />,
        );

        const resetButton = screen.getByRole('button', { name: 'Clear selected option' });
        fireEvent.focus(resetButton);

        expect(resetButton).not.toHaveAttribute('title');
        expect(screen.getByRole('tooltip')).toHaveTextContent('Clear selected option');

        fireEvent.click(resetButton);

        expect(onResetSelect).toHaveBeenCalledOnce();
    });

    test('does not offer reset without an active option or while loading', () => {
        const { rerender } = render(
            <Select
                activeOption={null}
                ariaLabel="Choose color"
                options={[oceanOption]}
                tid="color-select"
                onResetSelect={vi.fn()}
                onSelectOption={vi.fn()}
            />,
        );

        expect(screen.queryByRole('button', { name: 'Clear selected option' })).not.toBeInTheDocument();

        rerender(
            <Select
                isLoading
                activeOption={oceanOption}
                ariaLabel="Choose color"
                options={[oceanOption]}
                tid="color-select"
                onResetSelect={vi.fn()}
                onSelectOption={vi.fn()}
            />,
        );

        expect(screen.queryByRole('button', { name: 'Clear selected option' })).not.toBeInTheDocument();
    });
});

describe('Select active option', () => {
    test('truncates a long label to the available width', () => {
        render(
            <Select
                activeOption={{ label: 'A very long product variant name', value: 'long-variant' }}
                ariaLabel="Choose variant"
                options={[]}
                tid="variant-select"
                onSelectOption={vi.fn()}
            />,
        );

        expect(screen.getByText('A very long product variant name')).toHaveClass('block', 'truncate');
    });
});

describe('Select closing', () => {
    test('closes after clicking outside even when a parent stops bubbling mouse events', () => {
        render(
            // biome-ignore lint/a11y/noNoninteractiveElementInteractions: Emulates the popup event boundary from production.
            <dialog open onMouseDown={(event) => event.stopPropagation()}>
                <Select
                    activeOption={null}
                    ariaLabel="Choose color"
                    options={[oceanOption]}
                    tid="color-select"
                    onSelectOption={vi.fn()}
                />

                <textarea aria-label="Review text" />
            </dialog>,
        );

        const selectToggleButton = screen.getByRole('button', { name: 'Choose color' });
        fireEvent.click(selectToggleButton);
        expect(selectToggleButton).toHaveAttribute('aria-expanded', 'true');

        fireEvent.mouseDown(screen.getByRole('textbox', { name: 'Review text' }));
        expect(selectToggleButton).toHaveAttribute('aria-expanded', 'false');
    });
});
