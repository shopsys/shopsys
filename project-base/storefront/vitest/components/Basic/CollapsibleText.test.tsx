import { fireEvent, render, screen } from '@testing-library/react';
import { CollapsibleText } from 'components/Basic/CollapsibleText/CollapsibleText';
import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest';

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({
        t: (key: string) => key,
    }),
}));

describe('CollapsibleText', () => {
    beforeEach(() => {
        vi.stubGlobal(
            'ResizeObserver',
            class ResizeObserver {
                observe() {}
                unobserve() {}
                disconnect() {}
            },
        );
    });

    afterEach(() => {
        vi.unstubAllGlobals();
    });

    test('keeps the default readable width', () => {
        const { container } = render(<CollapsibleText text="Description" />);
        const textWrapper = container.querySelector('.user-text')?.parentElement;

        expect(textWrapper).toHaveClass('max-w-2xl');
    });

    test('allows a page to widen the text without retaining the default cap', () => {
        const { container } = render(<CollapsibleText text="Description" textClassName="max-w-5xl" />);
        const textWrapper = container.querySelector('.user-text')?.parentElement;

        expect(textWrapper).toHaveClass('max-w-5xl');
        expect(textWrapper).not.toHaveClass('max-w-2xl');
    });

    test('retries scrolling to the top when closing is interrupted', () => {
        const scrollTo = vi.fn();
        vi.stubGlobal('scrollY', 410);
        vi.stubGlobal('scrollTo', scrollTo);

        render(<CollapsibleText text="Description" />);

        const button = screen.getByRole('button');
        fireEvent.click(button);
        fireEvent.click(button);
        fireEvent.click(button);

        expect(scrollTo).toHaveBeenCalledTimes(2);
        expect(button).toHaveAttribute('aria-expanded', 'true');

        vi.stubGlobal('scrollY', 0);
        fireEvent.scroll(window);

        expect(button).toHaveAttribute('aria-expanded', 'false');
    });
});
