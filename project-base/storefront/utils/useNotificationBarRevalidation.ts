import { useNotificationBars } from 'graphql/requests/notificationBars/queries/NotificationBarsQuery.generated';
import { useEffect } from 'react';

const DEFAULT_POLLING_INTERVAL_MS = 300_000; // 5 minutes (matches @redisCache TTL)

const isFutureDate = (dateString: string | null): boolean => {
    if (dateString === null) {
        return true;
    }

    const timestamp = new Date(dateString).getTime();

    if (isNaN(timestamp)) {
        return true;
    }

    return Date.now() < timestamp;
};

export const useNotificationBarsWithRevalidation = (pollingIntervalMs = DEFAULT_POLLING_INTERVAL_MS) => {
    const [{ data: notificationBarsData }, fetchNotificationBars] = useNotificationBars();

    useEffect(() => {
        const intervalId = setInterval(() => {
            fetchNotificationBars({ requestPolicy: 'network-only' });
        }, pollingIntervalMs);

        return () => clearInterval(intervalId);
    }, [fetchNotificationBars, pollingIntervalMs]);

    const activeNotificationBars = notificationBarsData?.notificationBars?.filter((notification) =>
        isFutureDate(notification.validityTo),
    );

    return { notificationBarsData, activeNotificationBars, fetchNotificationBars };
};
