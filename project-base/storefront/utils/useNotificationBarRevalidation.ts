import dayjs from 'dayjs';
import minMax from 'dayjs/plugin/minMax';
import { TypeNotificationBarsFragment } from 'graphql/requests/notificationBars/fragments/NotificationBarsFragment.generated';
import { useNotificationBars } from 'graphql/requests/notificationBars/queries/NotificationBarsQuery.generated';
import { useMemo } from 'react';

dayjs.extend(minMax);

const getValidityDateTimes = (notificationBars: TypeNotificationBarsFragment[]) =>
    notificationBars
        .map((notification) => dayjs(notification.validityTo))
        .filter((validity) => dayjs().isBefore(validity));

export const useNotificationBarsWithRevalidation = (fromCache = true) => {
    const [{ data: notificationBarsData }, fetchNotificationBars] = useNotificationBars({
        requestPolicy: fromCache ? 'cache-first' : 'network-only',
    });

    const nextRevalidationTime =
        (notificationBarsData?.notificationBars &&
            dayjs.min(...getValidityDateTimes(notificationBarsData.notificationBars))) ??
        dayjs();

    const activeNotificationBars = useMemo(
        () =>
            notificationBarsData?.notificationBars?.filter(
                (notification) => notification.validityTo === null || dayjs().isBefore(dayjs(notification.validityTo)),
            ),
        [notificationBarsData],
    );

    return { notificationBarsData, activeNotificationBars, fetchNotificationBars, nextRevalidationTime };
};
