import { render } from '@testing-library/react';
import { MediaCarouselPagination } from 'components/Basic/MediaCarousel/MediaCarouselPagination';
import { describe, expect, test } from 'vitest';

describe('MediaCarouselPagination', () => {
    test('renders regular, medium, and small dots', () => {
        const { container } = render(<MediaCarouselPagination itemCount={10} selectedIndex={4} />);
        const dotSlots = container.querySelectorAll('[aria-hidden="true"] > div > span');

        expect(Array.from(dotSlots).some((dotSlot) => dotSlot.classList.contains('w-3'))).toBe(true);
        expect(Array.from(dotSlots).some((dotSlot) => dotSlot.classList.contains('w-2.5'))).toBe(true);
        expect(Array.from(dotSlots).some((dotSlot) => dotSlot.classList.contains('w-2'))).toBe(true);
    });

    test('moves the dot window only after the selected item leaves the three regular dots', () => {
        const { container, rerender } = render(<MediaCarouselPagination itemCount={10} selectedIndex={0} />);
        const getDotSlots = () => container.querySelectorAll<HTMLSpanElement>('div[aria-hidden="true"] > div > span');
        const getVisibleDotIndexes = () =>
            Array.from(getDotSlots()).flatMap((dotSlot, index) =>
                dotSlot.classList.contains('w-0') || dotSlot.classList.contains('size-0') ? [] : [index],
            );
        const getRegularDotIndexes = () =>
            Array.from(getDotSlots()).flatMap((dotSlot, index) =>
                dotSlot.firstElementChild?.classList.contains('size-2') ? [index] : [],
            );

        expect(getDotSlots()).toHaveLength(10);
        expect(getVisibleDotIndexes()).toEqual([0, 1, 2, 3, 4]);
        expect(getRegularDotIndexes()).toEqual([0, 1, 2]);

        rerender(<MediaCarouselPagination itemCount={10} selectedIndex={1} />);
        rerender(<MediaCarouselPagination itemCount={10} selectedIndex={2} />);

        expect(getVisibleDotIndexes()).toEqual([0, 1, 2, 3, 4]);
        expect(getRegularDotIndexes()).toEqual([0, 1, 2]);

        rerender(<MediaCarouselPagination itemCount={10} selectedIndex={3} />);

        expect(getVisibleDotIndexes()).toEqual([0, 1, 2, 3, 4, 5]);
        expect(getRegularDotIndexes()).toEqual([1, 2, 3]);

        rerender(<MediaCarouselPagination itemCount={10} selectedIndex={4} />);

        expect(getVisibleDotIndexes()).toEqual([0, 1, 2, 3, 4, 5, 6]);
        expect(getRegularDotIndexes()).toEqual([2, 3, 4]);

        rerender(<MediaCarouselPagination itemCount={10} selectedIndex={3} />);
        rerender(<MediaCarouselPagination itemCount={10} selectedIndex={2} />);

        expect(getVisibleDotIndexes()).toEqual([0, 1, 2, 3, 4, 5, 6]);
        expect(getRegularDotIndexes()).toEqual([2, 3, 4]);

        rerender(<MediaCarouselPagination itemCount={10} selectedIndex={1} />);

        expect(getVisibleDotIndexes()).toEqual([0, 1, 2, 3, 4, 5]);
        expect(getRegularDotIndexes()).toEqual([1, 2, 3]);
    });
});
