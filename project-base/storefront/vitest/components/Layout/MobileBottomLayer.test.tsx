import { render, screen } from '@testing-library/react';
import { MobileBottomLayer } from 'components/Layout/MobileBottomLayer/MobileBottomLayer';
import { describe, expect, test, vi } from 'vitest';

vi.mock('components/Layout/Header/MobileBottomNavigation/MobileBottomNavigation', () => ({
    MobileBottomNavigation: () => <nav data-testid="mobile-bottom-navigation" />,
}));

describe('MobileBottomLayer', () => {
    test('stacks contextual content above navigation in one fixed container', () => {
        const { container } = render(
            <MobileBottomLayer>
                <div data-testid="contextual-content" />
            </MobileBottomLayer>,
        );

        const layer = container.firstElementChild;
        const contextualContent = screen.getByTestId('contextual-content');
        const navigation = screen.getByTestId('mobile-bottom-navigation');
        const contextualContentSlot = contextualContent.parentElement;

        expect(layer).toHaveClass('fixed', '-bottom-px', 'z-overlay', 'vl:contents');
        expect(layer).toContainElement(contextualContent);
        expect(layer).toContainElement(navigation);
        expect(contextualContentSlot).toHaveClass('pointer-events-none', 'absolute', 'bottom-full', 'vl:contents');
        expect(contextualContentSlot?.nextElementSibling).toBe(navigation);
    });
});
