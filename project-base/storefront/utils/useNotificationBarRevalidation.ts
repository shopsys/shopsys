import { useNotificationBars } from 'graphql/requests/notificationBars/queries/NotificationBarsQuery.generated';
import { useEffect, useMemo } from 'react';

const DEFAULT_POLLING_INTERVAL_MS = 300_000; // 5 minutes (matches @redisCache TTL)

const isFutureDate = (dateString: string | null): boolean => {
    if (dateString === null) {
        return true;
    }
    return Date.now() < new Date(dateString).getTime();
};

export const useNotificationBarsWithRevalidation = (pollingIntervalMs = DEFAULT_POLLING_INTERVAL_MS) => {
    const [{ data: notificationBarsData }, fetchNotificationBars] = useNotificationBars();

    useEffect(() => {
        const intervalId = setInterval(() => {
            fetchNotificationBars({ requestPolicy: 'network-only' });
        }, pollingIntervalMs);

        return () => clearInterval(intervalId);
    }, [fetchNotificationBars, pollingIntervalMs]);

    const activeNotificationBars = useMemo(
        () => notificationBarsData?.notificationBars?.filter((notification) => isFutureDate(notification.validityTo)),
        [notificationBarsData],
    );

    return { notificationBarsData, activeNotificationBars, fetchNotificationBars };
};
