import { fireEvent, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { Select } from 'components/Forms/Select/Select';
import { domAnimation, LazyMotion } from 'framer-motion';
import { StrictMode, useState } from 'react';
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

const colorOptions = [
    { label: 'Ocean', value: { name: 'Ocean', availability: 'Available', price: '100 Kč' } },
    { label: 'Forest', value: { name: 'Forest', availability: 'On request', price: '200 Kč' } },
];

const ColorSelect = ({ richContent = false }: { richContent?: boolean }) => {
    const [activeOption, setActiveOption] = useState<(typeof colorOptions)[number] | null>(null);
    const renderColor = ({ value }: (typeof colorOptions)[number]) => (
        <span>
            {value.name} <span>{value.availability}</span> <span>{value.price}</span>
        </span>
    );

    return (
        <LazyMotion features={domAnimation}>
            {/* JSDOM does not load Tailwind; SelectList uses block! to override the animation's display: none. */}
            <style>{'.block\\! { display: block !important; }'}</style>
            <Select
                activeOption={activeOption}
                ariaLabel="Choose color"
                label="Color"
                options={colorOptions}
                renderOption={richContent ? renderColor : undefined}
                renderValue={richContent ? renderColor : undefined}
                tid="color-select"
                onSelectOption={setActiveOption}
            />
        </LazyMotion>
    );
};

describe('Select content and keyboard interaction', () => {
    test.each([
        { richContent: false, selectedName: 'ColorForest', optionName: 'Forest' },
        { richContent: true, selectedName: 'Forest On request 200 Kč', optionName: 'Forest On request 200 Kč' },
    ])('selects and displays a value with rich content: $richContent', async ({
        richContent,
        selectedName,
        optionName,
    }) => {
        const user = userEvent.setup();
        render(<ColorSelect richContent={richContent} />);

        await user.click(screen.getByRole('button', { name: 'Choose color' }));
        await user.click(await screen.findByRole('option', { name: optionName }));

        expect(screen.getByRole('button', { name: selectedName })).toBeInTheDocument();
        await waitFor(() => expect(screen.queryByRole('listbox')).not.toBeInTheDocument());
        expect(screen.getByRole('button', { name: 'Choose color' })).toHaveFocus();
    });

    test('opens with arrows, navigates with Home and End, and selects with Space', async () => {
        const user = userEvent.setup();
        render(
            <StrictMode>
                <ColorSelect richContent />
            </StrictMode>,
        );
        const trigger = screen.getByRole('button', { name: 'Choose color' });

        await user.tab();
        expect(trigger).toHaveFocus();
        await user.keyboard('{ArrowDown}');
        expect(await screen.findByRole('option', { name: 'Ocean Available 100 Kč' })).toHaveFocus();
        await user.keyboard('{End}');
        expect(await screen.findByRole('option', { name: 'Forest On request 200 Kč' })).toHaveFocus();
        await user.keyboard('{Home}');
        expect(screen.getByRole('option', { name: 'Ocean Available 100 Kč' })).toHaveFocus();
        await user.keyboard('{ArrowDown} ');

        expect(screen.getByRole('button', { name: 'Forest On request 200 Kč' })).toBeInTheDocument();
        await waitFor(() => expect(trigger).toHaveFocus());
    });

    test('Escape closes the list and restores focus before reaching the surrounding popup', async () => {
        const user = userEvent.setup();
        const onWindowKeyDown = vi.fn();
        render(<ColorSelect richContent />);
        const trigger = screen.getByRole('button', { name: 'Choose color' });
        await user.tab();
        await user.keyboard('{ArrowUp}');
        expect(await screen.findByRole('option', { name: 'Forest On request 200 Kč' })).toHaveFocus();
        window.addEventListener('keydown', onWindowKeyDown);

        try {
            await user.keyboard('{Escape}');

            expect(onWindowKeyDown).not.toHaveBeenCalled();
            await waitFor(() => expect(screen.queryByRole('listbox')).not.toBeInTheDocument());
            expect(trigger).toHaveFocus();

            await user.keyboard('{Escape}');

            expect(onWindowKeyDown).toHaveBeenCalledOnce();
        } finally {
            window.removeEventListener('keydown', onWindowKeyDown);
        }
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
