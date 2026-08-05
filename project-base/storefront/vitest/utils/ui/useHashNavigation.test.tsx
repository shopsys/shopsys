import { fireEvent, render, screen } from '@testing-library/react';
import { useRef } from 'react';
import { useHashNavigation } from 'utils/ui/useHashNavigation';
import { beforeEach, describe, expect, test, vi } from 'vitest';

const scrollIntoViewMock = vi.fn();

const HashNavigation = () => {
    const parametersRef = useRef<HTMLElement>(null);
    const reviewsRef = useRef<HTMLElement>(null);
    const { activeSection } = useHashNavigation([
        { id: 'parameters', ref: parametersRef },
        { id: 'reviews', ref: reviewsRef },
    ]);

    return (
        <>
            <a href="#reviews">Reviews</a>
            <section ref={parametersRef}>Parameters</section>
            <section ref={reviewsRef}>Reviews section</section>
            <output>{activeSection}</output>
        </>
    );
};

describe('useHashNavigation', () => {
    beforeEach(() => {
        window.history.replaceState(null, '', '#parameters');
        window.HTMLElement.prototype.scrollIntoView = scrollIntoViewMock;
    });

    test('keeps the linked section active when navigating through a same-page anchor', () => {
        render(<HashNavigation />);
        scrollIntoViewMock.mockClear();

        const wasClickHandledNatively = fireEvent.click(screen.getByRole('link', { name: 'Reviews' }));

        expect(wasClickHandledNatively).toBe(false);
        expect(window.location.hash).toBe('#reviews');
        expect(screen.getByRole('status')).toHaveTextContent('reviews');
        expect(scrollIntoViewMock).toHaveBeenCalledWith({ behavior: 'smooth' });
    });
});
