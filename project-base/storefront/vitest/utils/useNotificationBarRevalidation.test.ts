import { act, renderHook } from '@testing-library/react';
import { useNotificationBarsWithRevalidation } from 'utils/useNotificationBarRevalidation';
import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest';

const mockFetchNotificationBars = vi.fn();
const mockNotificationBars = vi.fn();

vi.mock('graphql/requests/notificationBars/queries/NotificationBarsQuery.generated', () => ({
    useNotificationBars: () => {
        return [
            {
                data: mockNotificationBars(),
            },
            mockFetchNotificationBars,
        ];
    },
}));

describe('useNotificationBarsWithRevalidation', () => {
    beforeEach(() => {
        vi.useFakeTimers();
        // Set a fixed current time for deterministic tests
        vi.setSystemTime(new Date('2024-03-15T12:00:00Z'));
        vi.clearAllMocks();
    });

    afterEach(() => {
        vi.useRealTimers();
        vi.restoreAllMocks();
    });

    describe('active notification filtering', () => {
        test('returns all notifications when all are valid', () => {
            mockNotificationBars.mockReturnValue({
                notificationBars: [
                    { id: '1', text: 'Bar 1', validityTo: '2024-03-20T12:00:00Z' },
                    { id: '2', text: 'Bar 2', validityTo: '2024-03-25T12:00:00Z' },
                ],
            });

            const { result } = renderHook(() => useNotificationBarsWithRevalidation());

            expect(result.current.activeNotificationBars).toHaveLength(2);
        });

        test('filters out expired notifications', () => {
            mockNotificationBars.mockReturnValue({
                notificationBars: [
                    { id: '1', text: 'Active', validityTo: '2024-03-20T12:00:00Z' },
                    { id: '2', text: 'Expired', validityTo: '2024-03-10T12:00:00Z' },
                ],
            });

            const { result } = renderHook(() => useNotificationBarsWithRevalidation());

            expect(result.current.activeNotificationBars).toHaveLength(1);
            expect(result.current.activeNotificationBars?.[0].text).toBe('Active');
        });

        test('includes notifications with null validityTo', () => {
            mockNotificationBars.mockReturnValue({
                notificationBars: [
                    { id: '1', text: 'Permanent', validityTo: null },
                    { id: '2', text: 'Temporary', validityTo: '2024-03-20T12:00:00Z' },
                ],
            });

            const { result } = renderHook(() => useNotificationBarsWithRevalidation());

            expect(result.current.activeNotificationBars).toHaveLength(2);
        });

        test('treats notifications with invalid date strings as active (defensive behavior)', () => {
            mockNotificationBars.mockReturnValue({
                notificationBars: [
                    { id: '1', text: 'Invalid date', validityTo: 'not-a-valid-date' },
                    { id: '2', text: 'Valid', validityTo: '2024-03-20T12:00:00Z' },
                ],
            });

            const { result } = renderHook(() => useNotificationBarsWithRevalidation());

            // Invalid dates are treated as "future" to avoid silently dropping notifications
            expect(result.current.activeNotificationBars).toHaveLength(2);
        });

        test('returns empty array when all notifications are expired', () => {
            mockNotificationBars.mockReturnValue({
                notificationBars: [
                    { id: '1', text: 'Expired 1', validityTo: '2024-03-01T12:00:00Z' },
                    { id: '2', text: 'Expired 2', validityTo: '2024-03-10T12:00:00Z' },
                ],
            });

            const { result } = renderHook(() => useNotificationBarsWithRevalidation());

            expect(result.current.activeNotificationBars).toHaveLength(0);
        });

        test('handles edge case when notification expires at current time', () => {
            mockNotificationBars.mockReturnValue({
                notificationBars: [{ id: '1', text: 'Expiring now', validityTo: '2024-03-15T12:00:00Z' }],
            });

            const { result } = renderHook(() => useNotificationBarsWithRevalidation());

            // Notification expiring exactly at current time should be filtered out
            expect(result.current.activeNotificationBars).toHaveLength(0);
        });
    });

    describe('polling behavior', () => {
        test('does not call fetchNotificationBars immediately', () => {
            mockNotificationBars.mockReturnValue({
                notificationBars: [],
            });

            renderHook(() => useNotificationBarsWithRevalidation());

            expect(mockFetchNotificationBars).not.toHaveBeenCalled();
        });

        test('calls fetchNotificationBars after default polling interval (5 min, matches Redis TTL)', () => {
            mockNotificationBars.mockReturnValue({
                notificationBars: [],
            });

            renderHook(() => useNotificationBarsWithRevalidation());

            act(() => {
                vi.advanceTimersByTime(300_000); // 5 minutes
            });

            expect(mockFetchNotificationBars).toHaveBeenCalledTimes(1);
        });

        test('calls fetchNotificationBars multiple times over multiple intervals', () => {
            mockNotificationBars.mockReturnValue({
                notificationBars: [],
            });

            renderHook(() => useNotificationBarsWithRevalidation());

            act(() => {
                vi.advanceTimersByTime(900_000); // 15 minutes = 3 intervals of 5 min
            });

            expect(mockFetchNotificationBars).toHaveBeenCalledTimes(3);
        });

        test('uses custom polling interval when provided', () => {
            mockNotificationBars.mockReturnValue({
                notificationBars: [],
            });

            const customInterval = 30_000; // 30 seconds
            renderHook(() => useNotificationBarsWithRevalidation(customInterval));

            act(() => {
                vi.advanceTimersByTime(60_000); // 60 seconds = 2 custom intervals
            });

            expect(mockFetchNotificationBars).toHaveBeenCalledTimes(2);
        });

        test('calls fetchNotificationBars with network-only request policy to bypass URQL cache', () => {
            mockNotificationBars.mockReturnValue({
                notificationBars: [],
            });

            renderHook(() => useNotificationBarsWithRevalidation());

            act(() => {
                vi.advanceTimersByTime(300_000); // 5 minutes
            });

            expect(mockFetchNotificationBars).toHaveBeenCalledWith({ requestPolicy: 'network-only' });
        });

        test('cleans up interval on unmount', () => {
            mockNotificationBars.mockReturnValue({
                notificationBars: [],
            });

            const { unmount } = renderHook(() => useNotificationBarsWithRevalidation());

            unmount();

            act(() => {
                vi.advanceTimersByTime(120_000);
            });

            expect(mockFetchNotificationBars).not.toHaveBeenCalled();
        });
    });

    describe('data fetching', () => {
        test('exposes fetchNotificationBars function', () => {
            mockNotificationBars.mockReturnValue({
                notificationBars: [],
            });

            const { result } = renderHook(() => useNotificationBarsWithRevalidation());

            expect(result.current.fetchNotificationBars).toBe(mockFetchNotificationBars);
        });

        test('exposes raw notification bars data', () => {
            const notificationData = {
                notificationBars: [{ id: '1', text: 'Test', validityTo: '2024-03-20T12:00:00Z' }],
            };
            mockNotificationBars.mockReturnValue(notificationData);

            const { result } = renderHook(() => useNotificationBarsWithRevalidation());

            expect(result.current.notificationBarsData).toEqual(notificationData);
        });
    });

    describe('empty data handling', () => {
        test('handles empty notification bars array', () => {
            mockNotificationBars.mockReturnValue({
                notificationBars: [],
            });

            const { result } = renderHook(() => useNotificationBarsWithRevalidation());

            expect(result.current.activeNotificationBars).toHaveLength(0);
        });

        test('handles undefined notification bars data', () => {
            mockNotificationBars.mockReturnValue(undefined);

            const { result } = renderHook(() => useNotificationBarsWithRevalidation());

            expect(result.current.activeNotificationBars).toBeUndefined();
        });
    });
});
