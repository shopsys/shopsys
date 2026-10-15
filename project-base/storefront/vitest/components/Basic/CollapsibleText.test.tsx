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

    test('collapses in place when the description starts below the sticky header', () => {
        const scrollTo = vi.fn();
        vi.stubGlobal('scrollY', 100);
        vi.stubGlobal('scrollTo', scrollTo);
        const { container } = render(<CollapsibleText text="Description" />);
        const text = container.querySelector('.user-text') as HTMLElement;
        text.style.scrollMarginTop = '80px';
        vi.spyOn(text, 'getBoundingClientRect').mockReturnValue({ top: 100 } as DOMRect);
        const button = screen.getByRole('button');

        fireEvent.click(button);
        fireEvent.click(button);

        expect(scrollTo).not.toHaveBeenCalled();
        expect(button).toHaveAttribute('aria-expanded', 'false');
    });

    test('retries scrolling to the description when closing is interrupted', () => {
        const scrollTo = vi.fn();
        vi.stubGlobal('scrollY', 410);
        vi.stubGlobal('scrollTo', scrollTo);

        const { container } = render(<CollapsibleText text="Description" />);
        const text = container.querySelector('.user-text') as HTMLElement;
        text.style.scrollMarginTop = '80px';
        vi.spyOn(text, 'getBoundingClientRect').mockReturnValue({ top: -100 } as DOMRect);

        const button = screen.getByRole('button');
        fireEvent.click(button);
        fireEvent.click(button);
        fireEvent.click(button);

        expect(scrollTo).toHaveBeenCalledTimes(2);
        expect(scrollTo).toHaveBeenLastCalledWith({ top: 230, behavior: 'smooth' });
        expect(button).toHaveAttribute('aria-expanded', 'true');

        vi.stubGlobal('scrollY', 230);
        fireEvent.scroll(window);

        expect(button).toHaveAttribute('aria-expanded', 'false');
    });
});
