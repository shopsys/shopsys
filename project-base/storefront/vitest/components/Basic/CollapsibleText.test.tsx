import { fireEvent, render, screen } from '@testing-library/react';
import { CollapsibleText } from 'components/Basic/CollapsibleText/CollapsibleText';
import { useReducedMotion } from 'framer-motion';
import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest';

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({
        t: (key: string) => key,
    }),
}));

vi.mock('framer-motion', async (importOriginal) => ({
    ...(await importOriginal<typeof import('framer-motion')>()),
    useReducedMotion: vi.fn(),
}));

describe('CollapsibleText', () => {
    beforeEach(() => {
        vi.mocked(useReducedMotion).mockReturnValue(false);
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

    const renderExpandedDescription = () => {
        vi.stubGlobal('scrollY', 410);
        const { container } = render(<CollapsibleText text="Description" />);
        const text = container.querySelector('.user-text') as HTMLElement;
        text.style.scrollMarginTop = '80px';
        vi.spyOn(text, 'getBoundingClientRect').mockImplementation(() => ({ top: 310 - window.scrollY }) as DOMRect);
        const button = screen.getByRole('button');
        fireEvent.click(button);

        return button;
    };

    test('waits until scrolling reaches the description before collapsing', () => {
        const scrollTo = vi.fn();
        vi.stubGlobal('scrollTo', scrollTo);
        const button = renderExpandedDescription();

        fireEvent.click(button);
        vi.stubGlobal('scrollY', 300);
        fireEvent.scroll(window);

        expect(scrollTo).toHaveBeenCalledWith({ top: 230, behavior: 'smooth' });
        expect(button).toHaveAttribute('aria-expanded', 'true');

        vi.stubGlobal('scrollY', 230);
        fireEvent.scroll(window);

        expect(button).toHaveAttribute('aria-expanded', 'false');
    });

    test('keeps the description open after scrolling away and allows closing again', () => {
        const scrollTo = vi.fn();
        vi.stubGlobal('scrollTo', scrollTo);
        const button = renderExpandedDescription();
        fireEvent.click(button);

        vi.stubGlobal('scrollY', 1200);
        fireEvent.scroll(window);
        vi.stubGlobal('scrollY', 230);
        fireEvent.scroll(window);

        expect(button).toHaveAttribute('aria-expanded', 'true');

        fireEvent.click(button);

        expect(button).toHaveAttribute('aria-expanded', 'false');
    });

    test.each(['wheel', 'touchstart', 'keydown', 'pointerdown', 'scrollend'])(
        'cancels pending collapse on %s before reaching the description',
        (eventName) => {
            vi.stubGlobal('scrollTo', vi.fn());
            const button = renderExpandedDescription();
            fireEvent.click(button);

            fireEvent(window, new Event(eventName));
            vi.stubGlobal('scrollY', 230);
            fireEvent.scroll(window);

            expect(button).toHaveAttribute('aria-expanded', 'true');
        },
    );

    test('can retry closing after interrupted scrolling', () => {
        const scrollTo = vi.fn();
        vi.stubGlobal('scrollTo', scrollTo);
        const button = renderExpandedDescription();
        fireEvent.click(button);
        fireEvent.wheel(window);

        fireEvent.click(button);
        vi.stubGlobal('scrollY', 230);
        fireEvent.scroll(window);

        expect(scrollTo).toHaveBeenCalledTimes(2);
        expect(button).toHaveAttribute('aria-expanded', 'false');
    });

    test.each([false, true])('collapses after an immediate scroll (reduced motion: %s)', (reducedMotion) => {
        vi.mocked(useReducedMotion).mockReturnValue(reducedMotion);
        const button = renderExpandedDescription();
        const scrollTo = vi.fn(({ top }: ScrollToOptions) => {
            expect(button).toHaveAttribute('aria-expanded', 'true');
            vi.stubGlobal('scrollY', top);
        });
        vi.stubGlobal('scrollTo', scrollTo);

        fireEvent.click(button);

        expect(scrollTo).toHaveBeenCalledWith({ top: 230, behavior: reducedMotion ? 'instant' : 'smooth' });
        expect(button).toHaveAttribute('aria-expanded', 'false');
    });
});
