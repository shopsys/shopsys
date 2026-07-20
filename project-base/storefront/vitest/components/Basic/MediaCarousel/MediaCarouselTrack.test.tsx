import { act, fireEvent, render, screen } from '@testing-library/react';
import {
    MediaCarouselItem,
    MediaCarouselTrack,
    MediaCarouselTrackHandle,
} from 'components/Basic/MediaCarousel/MediaCarouselTrack';
import { createRef } from 'react';
import { describe, expect, test, vi } from 'vitest';

const images: MediaCarouselItem[] = Array.from({ length: 5 }, (_, index) => ({
    __typename: 'Image',
    name: `Image ${index + 1}`,
    url: `/image-${index + 1}.jpg`,
}));

describe('MediaCarouselTrack', () => {
    test('realigns the selected slide after the track width changes', () => {
        let resizeObserverCallback: ResizeObserverCallback | undefined;

        vi.stubGlobal(
            'ResizeObserver',
            class ResizeObserver {
                constructor(callback: ResizeObserverCallback) {
                    resizeObserverCallback = callback;
                }

                observe() {}
                unobserve() {}
                disconnect() {}
            },
        );

        try {
            render(
                <MediaCarouselTrack
                    ariaLabel="Media track"
                    initialIndex={3}
                    items={images}
                    selectedIndex={3}
                    onSelectedIndexChange={vi.fn()}
                    renderItem={(_, index) => <span data-index={index} />}
                />,
            );

            const track = screen.getByRole('list', { name: 'Media track' });
            Object.defineProperty(track, 'clientWidth', { configurable: true, value: 400 });

            act(() => {
                resizeObserverCallback?.([], {} as ResizeObserver);
            });

            expect(track.scrollLeft).toBe(1_200);

            Object.defineProperty(track, 'clientWidth', { configurable: true, value: 425 });

            act(() => {
                resizeObserverCallback?.([], {} as ResizeObserver);
            });

            expect(track.scrollLeft).toBe(1_275);
        } finally {
            vi.unstubAllGlobals();
        }
    });

    test('does not publish or load intermediate slides during a programmatic smooth scroll', () => {
        vi.useFakeTimers();

        try {
            const onSelectedIndexChange = vi.fn();
            const trackRef = createRef<MediaCarouselTrackHandle>();

            render(
                <MediaCarouselTrack
                    ariaLabel="Media track"
                    items={images}
                    ref={trackRef}
                    selectedIndex={0}
                    onSelectedIndexChange={onSelectedIndexChange}
                    renderItem={(_, index, isLoaded) => (
                        <span data-index={index} data-loaded={isLoaded ? 'true' : 'false'} />
                    )}
                />,
            );

            const track = screen.getByRole('list', { name: 'Media track' });
            Object.defineProperty(track, 'clientWidth', { configurable: true, value: 500 });
            Object.defineProperty(track, 'scrollTo', { configurable: true, value: vi.fn() });

            act(() => {
                trackRef.current?.scrollToIndex(4);
            });

            expect(onSelectedIndexChange).toHaveBeenCalledExactlyOnceWith(4);
            onSelectedIndexChange.mockClear();

            track.scrollLeft = 500;
            fireEvent.scroll(track);
            track.scrollLeft = 1_000;
            fireEvent.scroll(track);
            track.scrollLeft = 1_500;
            fireEvent.scroll(track);

            expect(onSelectedIndexChange).not.toHaveBeenCalled();
            expect(track.querySelector('[data-index="1"]')).toHaveAttribute('data-loaded', 'false');
            expect(track.querySelector('[data-index="2"]')).toHaveAttribute('data-loaded', 'false');
            expect(track.querySelector('[data-index="3"]')).toHaveAttribute('data-loaded', 'false');
            expect(track.querySelector('[data-index="4"]')).toHaveAttribute('data-loaded', 'true');

            act(() => {
                vi.advanceTimersByTime(100);
            });

            expect(track.scrollLeft).toBe(2_000);
            expect(onSelectedIndexChange.mock.calls.flat()).toEqual([4]);
        } finally {
            vi.useRealTimers();
        }
    });
});
