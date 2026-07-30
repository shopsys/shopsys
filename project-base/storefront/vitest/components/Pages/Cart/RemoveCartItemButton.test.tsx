import { fireEvent, screen } from '@testing-library/react';
import { RemoveCartItemButton } from 'components/Pages/Cart/RemoveCartItemButton';
import { describe, expect, test, vi } from 'vitest';
import { renderWithTooltipProvider as render } from 'vitest/helpers/renderWithTooltipProvider';

describe('RemoveCartItemButton', () => {
    test('shows the remove action in a custom tooltip', () => {
        render(
            <RemoveCartItemButton
                ariaLabel="Remove Test product from cart"
                title="Remove from cart"
                onRemoveFromCart={vi.fn()}
            />,
        );
        const button = screen.getByRole('button', { name: 'Remove Test product from cart' });

        fireEvent.focus(button);

        expect(button).not.toHaveAttribute('title');
        expect(screen.getByRole('tooltip')).toHaveTextContent('Remove from cart');
    });
});
