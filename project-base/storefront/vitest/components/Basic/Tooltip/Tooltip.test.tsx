import { act, fireEvent, screen } from '@testing-library/react';
import { Tooltip } from 'components/Basic/Tooltip/Tooltip';
import { createRef } from 'react';
import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest';
import { renderWithTooltipProvider as renderTooltip } from 'vitest/helpers/renderWithTooltipProvider';

describe('Tooltip', () => {
    beforeEach(() => {
        vi.useFakeTimers();
    });

    afterEach(() => {
        vi.useRealTimers();
    });

    test('opens after a short hover delay and closes immediately', () => {
        renderTooltip(
            <Tooltip label="Comparison">
                <button type="button">Compare</button>
            </Tooltip>,
        );
        const trigger = screen.getByRole('button', { name: 'Compare' });

        fireEvent.pointerMove(trigger, { pointerType: 'mouse' });
        act(() => {
            vi.advanceTimersByTime(249);
        });

        expect(screen.queryByRole('tooltip')).not.toBeInTheDocument();

        act(() => {
            vi.advanceTimersByTime(1);
        });

        expect(screen.getByRole('tooltip')).toHaveTextContent('Comparison');

        fireEvent.pointerLeave(trigger, { pointerType: 'mouse' });

        expect(screen.queryByRole('tooltip')).not.toBeInTheDocument();
    });

    test('does not open when hover ends before the delay', () => {
        renderTooltip(
            <Tooltip label="Wishlist">
                <button type="button">Wishlist</button>
            </Tooltip>,
        );
        const trigger = screen.getByRole('button', { name: 'Wishlist' });

        fireEvent.pointerMove(trigger, { pointerType: 'mouse' });
        fireEvent.pointerLeave(trigger, { pointerType: 'mouse' });
        act(() => {
            vi.advanceTimersByTime(250);
        });

        expect(screen.queryByRole('tooltip')).not.toBeInTheDocument();
    });

    test('opens immediately when the trigger receives focus', () => {
        renderTooltip(
            <Tooltip label="Stores">
                <button type="button">Stores</button>
            </Tooltip>,
        );

        fireEvent.focus(screen.getByRole('button', { name: 'Stores' }));

        expect(screen.getByRole('tooltip')).toHaveTextContent('Stores');
    });

    test('closes after the trigger is activated and preserves its click handler', () => {
        const onClick = vi.fn();
        renderTooltip(
            <Tooltip label="Wishlist">
                <button type="button" onClick={onClick}>
                    Wishlist
                </button>
            </Tooltip>,
        );
        const trigger = screen.getByRole('button', { name: 'Wishlist' });
        fireEvent.focus(trigger);

        fireEvent.click(trigger);

        expect(onClick).toHaveBeenCalledOnce();
        expect(screen.queryByRole('tooltip')).not.toBeInTheDocument();
    });

    test('does not open after activation while a delayed opening is pending', () => {
        renderTooltip(
            <Tooltip label="Wishlist">
                <button type="button">Wishlist</button>
            </Tooltip>,
        );
        const trigger = screen.getByRole('button', { name: 'Wishlist' });
        fireEvent.pointerMove(trigger, { pointerType: 'mouse' });

        fireEvent.click(trigger);
        act(() => {
            vi.advanceTimersByTime(250);
        });

        expect(screen.queryByRole('tooltip')).not.toBeInTheDocument();
    });

    test('opens the next tooltip immediately during the provider skip delay', () => {
        renderTooltip(
            <>
                <Tooltip label="Comparison">
                    <button type="button">Compare</button>
                </Tooltip>
                <Tooltip label="Wishlist">
                    <button type="button">Wishlist</button>
                </Tooltip>
            </>,
        );
        const compareTrigger = screen.getByRole('button', { name: 'Compare' });
        const wishlistTrigger = screen.getByRole('button', { name: 'Wishlist' });
        fireEvent.pointerMove(compareTrigger, { pointerType: 'mouse' });
        act(() => {
            vi.advanceTimersByTime(250);
        });
        fireEvent.pointerLeave(compareTrigger, { pointerType: 'mouse' });

        fireEvent.pointerMove(wishlistTrigger, { pointerType: 'mouse' });

        expect(screen.getByRole('tooltip')).toHaveTextContent('Wishlist');
    });

    test('preserves the trigger ref', () => {
        const triggerRef = createRef<HTMLButtonElement>();
        renderTooltip(
            <Tooltip label="Comparison">
                <button ref={triggerRef} type="button">
                    Compare
                </button>
            </Tooltip>,
        );

        expect(triggerRef.current).toBe(screen.getByRole('button', { name: 'Compare' }));
    });

    test('preserves the trigger when the tooltip becomes enabled', () => {
        const { rerender } = renderTooltip(
            <Tooltip disabled label="Remove from cart">
                <button type="button">Remove</button>
            </Tooltip>,
        );
        const trigger = screen.getByRole('button', { name: 'Remove' });

        fireEvent.focus(trigger);
        expect(screen.queryByRole('tooltip')).not.toBeInTheDocument();

        rerender(
            <Tooltip label="Remove from cart">
                <button type="button">Remove</button>
            </Tooltip>,
        );

        expect(screen.getByRole('button', { name: 'Remove' })).toBe(trigger);

        fireEvent.blur(trigger);
        fireEvent.focus(trigger);
        expect(screen.getByRole('tooltip')).toHaveTextContent('Remove from cart');
    });

    test('describes the trigger with the tooltip content when open', () => {
        renderTooltip(
            <Tooltip label="Add product to comparison">
                <button type="button">Compare</button>
            </Tooltip>,
        );
        const trigger = screen.getByRole('button', { name: 'Compare' });

        fireEvent.focus(trigger);

        expect(trigger).toHaveAccessibleDescription('Add product to comparison');
    });

    test('renders above fixed overlays', () => {
        const { container } = renderTooltip(
            <Tooltip label="Comparison">
                <button type="button">Compare</button>
            </Tooltip>,
        );

        fireEvent.focus(screen.getByRole('button', { name: 'Compare' }));

        expect(container.ownerDocument.querySelector('.tooltip')).toHaveClass('z-maximum');
    });

    test.each([
        {
            lifecycleEvent: 'the window loses focus',
            dispatchLifecycleEvent: () => fireEvent.blur(window),
        },
        {
            lifecycleEvent: 'the document visibility changes',
            dispatchLifecycleEvent: () => fireEvent(document, new Event('visibilitychange')),
        },
    ])('closes when $lifecycleEvent', ({ dispatchLifecycleEvent }) => {
        renderTooltip(
            <Tooltip label="Grid view">
                <button type="button">Grid view</button>
            </Tooltip>,
        );
        const trigger = screen.getByRole('button', { name: 'Grid view' });
        fireEvent.focus(trigger);
        expect(screen.getByRole('tooltip')).toHaveTextContent('Grid view');

        dispatchLifecycleEvent();

        expect(screen.queryByRole('tooltip')).not.toBeInTheDocument();
    });
});
