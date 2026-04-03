import { act, renderHook } from '@testing-library/react';
import { useCountdown } from 'utils/useCountdown';
import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest';

vi.mock('next/router', () => ({
    useRouter: () => ({
        reload: vi.fn(),
    }),
}));

describe('useCountdown', () => {
    beforeEach(() => {
        vi.useFakeTimers();
        vi.clearAllMocks();
    });

    afterEach(() => {
        act(() => {
            vi.runOnlyPendingTimers();
        });
        vi.useRealTimers();
    });

    const noopCallback = vi.fn();

    describe('initial state', () => {
        test('updates state immediately on next tick (not waiting for full interval)', () => {
            vi.setSystemTime(new Date('2024-03-15T12:00:00Z'));

            const { result } = renderHook(() => useCountdown('2024-03-16T12:00:00Z', noopCallback));

            // Before any timer advancement, still loading
            expect(result.current.isLoading).toBe(true);

            // Advance by just 1ms - should trigger the initial update
            act(() => {
                vi.advanceTimersByTime(1);
            });

            // Now should be loaded with correct initial values
            expect(result.current.isLoading).toBe(false);
            expect(result.current.days).toBe('01');
            expect(result.current.hours).toBe('00');
            expect(result.current.minutes).toBe('00');
            expect(result.current.seconds).toBe('00');
        });

        test('updates state after first interval', () => {
            vi.setSystemTime(new Date('2024-03-15T12:00:00Z'));

            const { result } = renderHook(() => useCountdown('2024-03-16T12:00:00Z', noopCallback));

            act(() => {
                vi.advanceTimersByTime(1000);
            });

            expect(result.current.isLoading).toBe(false);
            expect(result.current.days).toBe('00');
            expect(result.current.hours).toBe('23');
            expect(result.current.minutes).toBe('59');
            expect(result.current.seconds).toBe('59');
        });
    });

    describe('countdown calculations', () => {
        test('calculates days correctly for multi-day countdown', () => {
            vi.setSystemTime(new Date('2024-03-15T12:00:00Z'));

            const { result } = renderHook(() => useCountdown('2024-03-20T12:00:00Z', noopCallback));

            act(() => {
                vi.advanceTimersByTime(1000);
            });

            expect(result.current.days).toBe('04');
            expect(result.current.hours).toBe('23');
            expect(result.current.minutes).toBe('59');
            expect(result.current.seconds).toBe('59');
        });

        test('calculates time correctly for hours-only countdown', () => {
            vi.setSystemTime(new Date('2024-03-15T12:00:00Z'));

            const { result } = renderHook(() => useCountdown('2024-03-15T18:30:00Z', noopCallback));

            act(() => {
                vi.advanceTimersByTime(1000);
            });

            expect(result.current.days).toBe('00');
            expect(result.current.hours).toBe('06');
            expect(result.current.minutes).toBe('29');
            expect(result.current.seconds).toBe('59');
        });

        test('calculates time correctly for minutes-only countdown', () => {
            vi.setSystemTime(new Date('2024-03-15T12:00:00Z'));

            const { result } = renderHook(() => useCountdown('2024-03-15T12:30:00Z', noopCallback));

            act(() => {
                vi.advanceTimersByTime(1000);
            });

            expect(result.current.days).toBe('00');
            expect(result.current.hours).toBe('00');
            expect(result.current.minutes).toBe('29');
            expect(result.current.seconds).toBe('59');
        });

        test('decrements seconds correctly', () => {
            vi.setSystemTime(new Date('2024-03-15T12:00:00Z'));

            const { result } = renderHook(() => useCountdown('2024-03-15T12:01:00Z', noopCallback));

            act(() => {
                vi.advanceTimersByTime(1000);
            });

            expect(result.current.seconds).toBe('59');

            act(() => {
                vi.advanceTimersByTime(1000);
            });

            expect(result.current.seconds).toBe('58');
        });
    });

    describe('format padding', () => {
        test('pads single-digit values with zeros', () => {
            vi.setSystemTime(new Date('2024-03-15T12:00:00Z'));

            const { result } = renderHook(() => useCountdown('2024-03-15T12:01:05Z', noopCallback));

            act(() => {
                vi.advanceTimersByTime(1000);
            });

            expect(result.current.days).toBe('00');
            expect(result.current.hours).toBe('00');
            expect(result.current.minutes).toBe('01');
            expect(result.current.seconds).toBe('04');
        });

        test('does not pad double-digit values', () => {
            vi.setSystemTime(new Date('2024-03-15T12:00:00Z'));

            const { result } = renderHook(() => useCountdown('2024-04-15T12:00:00Z', noopCallback));

            act(() => {
                vi.advanceTimersByTime(1000);
            });

            expect(result.current.days).toBe('30');
        });
    });

    describe('callback execution', () => {
        test('calls custom callback when countdown ends', () => {
            vi.setSystemTime(new Date('2024-03-15T12:00:00Z'));
            const customCallback = vi.fn();

            // Start with 5 seconds remaining
            renderHook(() => useCountdown('2024-03-15T12:00:05Z', customCallback));

            // After first tick, 4 seconds remain
            act(() => {
                vi.advanceTimersByTime(1000);
            });
            expect(customCallback).not.toHaveBeenCalled();

            // After 5 more seconds, countdown should have ended
            act(() => {
                vi.advanceTimersByTime(5000);
            });
            expect(customCallback).toHaveBeenCalledTimes(1);
        });

        test('callback is called only once', () => {
            vi.setSystemTime(new Date('2024-03-15T12:00:00Z'));
            const customCallback = vi.fn();

            renderHook(() => useCountdown('2024-03-15T12:00:02Z', customCallback));

            act(() => {
                vi.advanceTimersByTime(10000);
            });

            expect(customCallback).toHaveBeenCalledTimes(1);
        });
    });

    describe('custom interval', () => {
        test('uses custom interval for updates', () => {
            vi.setSystemTime(new Date('2024-03-15T12:00:00Z'));

            const { result } = renderHook(() => useCountdown('2024-03-15T12:01:00Z', noopCallback, 500));

            act(() => {
                vi.advanceTimersByTime(500);
            });

            // After 500ms, should have updated
            expect(result.current.isLoading).toBe(false);

            // With 500ms interval, after 2 full seconds (4 intervals), seconds should change
            act(() => {
                vi.advanceTimersByTime(2000);
            });

            // The seconds should have decremented by 2 (from 59 to 57)
            expect(result.current.seconds).toBe('57');
        });
    });

    describe('edge cases', () => {
        test('handles already expired time', () => {
            vi.setSystemTime(new Date('2024-03-15T12:00:00Z'));
            const customCallback = vi.fn();

            renderHook(() => useCountdown('2024-03-14T12:00:00Z', customCallback));

            // Callback is triggered immediately on next tick (not waiting for full interval)
            act(() => {
                vi.advanceTimersByTime(1);
            });

            expect(customCallback).toHaveBeenCalled();
        });

        test('handles Date object as endTime', () => {
            vi.setSystemTime(new Date('2024-03-15T12:00:00Z'));

            const { result } = renderHook(() => useCountdown(new Date('2024-03-16T12:00:00Z'), noopCallback));

            act(() => {
                vi.advanceTimersByTime(1000);
            });

            expect(result.current.days).toBe('00');
            expect(result.current.hours).toBe('23');
        });

        test('cleans up interval on unmount', () => {
            vi.setSystemTime(new Date('2024-03-15T12:00:00Z'));
            const customCallback = vi.fn();

            const { unmount } = renderHook(() => useCountdown('2024-03-16T12:00:00Z', customCallback));

            act(() => {
                vi.advanceTimersByTime(1000);
            });

            unmount();

            // After unmount, no more callbacks should be triggered
            act(() => {
                vi.advanceTimersByTime(10000);
            });

            // The callback should not have been called since we unmounted
            // before the countdown ended
            expect(customCallback).not.toHaveBeenCalled();
        });
    });
});
