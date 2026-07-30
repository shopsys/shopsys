import { act, renderHook } from '@testing-library/react';
import { useScrollTop } from 'utils/ui/useScrollTop';
import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest';

describe('useScrollTop', () => {
    let intersectionObserverCallback: IntersectionObserverCallback;
    const disconnectMock = vi.fn();
    const observeMock = vi.fn();
    const observer = { disconnect: disconnectMock, observe: observeMock } as unknown as IntersectionObserver;

    beforeEach(() => {
        document.body.innerHTML = '<div id="sticky-trigger"></div>';
        vi.stubGlobal(
            'IntersectionObserver',
            class {
                constructor(callback: IntersectionObserverCallback) {
                    intersectionObserverCallback = callback;
                }

                disconnect = disconnectMock;
                observe = observeMock;
            },
        );
    });

    afterEach(() => {
        vi.unstubAllGlobals();
        vi.clearAllMocks();
    });

    test('activates after a short element passes the observer top boundary', () => {
        const setIsPastElement = vi.fn();
        renderHook(() => useScrollTop('sticky-trigger', setIsPastElement));

        act(() => {
            intersectionObserverCallback(
                [
                    {
                        boundingClientRect: { bottom: 150, top: 70 },
                        isIntersecting: false,
                        rootBounds: { top: 150 },
                    } as IntersectionObserverEntry,
                ],
                observer,
            );
        });

        expect(setIsPastElement).toHaveBeenCalledWith(true);
    });

    test('stays inactive while a non-intersecting element is below the viewport', () => {
        const setIsPastElement = vi.fn();
        renderHook(() => useScrollTop('sticky-trigger', setIsPastElement));

        act(() => {
            intersectionObserverCallback(
                [
                    {
                        boundingClientRect: { bottom: 400, top: 320 },
                        isIntersecting: false,
                        rootBounds: { top: 150 },
                    } as IntersectionObserverEntry,
                ],
                observer,
            );
        });

        expect(setIsPastElement).toHaveBeenCalledWith(false);
    });
});
