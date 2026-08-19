import { act, fireEvent, screen } from '@testing-library/react';
import { IconButton } from 'components/Forms/Button/IconButton';
import { createRef } from 'react';
import { describe, expect, test, vi } from 'vitest';
import { renderWithTooltipProvider as render } from 'vitest/helpers/renderWithTooltipProvider';

const TestIcon: SvgFC = (props) => <svg {...props} />;

describe('IconButton', () => {
    test('does not submit parent forms by default', () => {
        render(<IconButton Icon={TestIcon} title="Close popup" onClick={vi.fn()} />);

        expect(screen.getByRole('button', { name: 'Close popup' })).toHaveAttribute('type', 'button');
    });

    test('hides its icon from assistive technology', () => {
        render(<IconButton Icon={TestIcon} title="Close drawer" />);
        const button = screen.getByRole('button', { name: 'Close drawer' });

        expect(button.querySelector('svg')).toHaveAttribute('aria-hidden', 'true');
    });

    test('forwards native attributes and ref', () => {
        const buttonRef = createRef<HTMLButtonElement>();

        render(
            <IconButton
                Icon={TestIcon}
                aria-pressed
                ref={buttonRef}
                shape="rounded"
                size="small"
                title="Toggle view"
                variant="ghost"
            />,
        );
        const button = screen.getByRole('button', { name: 'Toggle view' });

        expect(button).toHaveAttribute('aria-pressed', 'true');
        expect(buttonRef.current).toBe(button);
    });

    test('shows an opt-in tooltip instead of the native title', () => {
        render(<IconButton Icon={TestIcon} title="Remove product" tooltipLabel="Remove product" onClick={vi.fn()} />);
        const button = screen.getByRole('button', { name: 'Remove product' });

        fireEvent.focus(button);

        expect(button).not.toHaveAttribute('title');
        expect(screen.getByRole('tooltip')).toHaveTextContent('Remove product');
    });

    test('shows its tooltip on hover when disabled', () => {
        vi.useFakeTimers();

        try {
            render(<IconButton disabled Icon={TestIcon} title="Remove product" tooltipLabel="Remove product" />);
            const button = screen.getByRole('button', { name: 'Remove product' });
            const tooltipTrigger = button.parentElement;

            expect(button).toBeDisabled();
            expect(tooltipTrigger).toHaveClass('inline-flex');

            fireEvent.pointerMove(tooltipTrigger!, { pointerType: 'mouse' });
            act(() => {
                vi.advanceTimersByTime(250);
            });

            expect(screen.getByRole('tooltip')).toHaveTextContent('Remove product');
        } finally {
            vi.useRealTimers();
        }
    });

    test('falls back to the title when aria label is empty', () => {
        render(<IconButton Icon={TestIcon} ariaLabel="" title="Close popup" />);

        expect(screen.getByRole('button', { name: 'Close popup' })).toBeInTheDocument();
    });
});
