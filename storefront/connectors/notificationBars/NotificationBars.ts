import { NotificationBarsFragmentApi, useNotificationBarsApi } from 'graphql/generated';
import { getFirstImageSize } from 'connectors/image/Image';
import { NotificationBarsType } from 'types/notificationBars';
import { useQueryError } from 'hooks/graphQl/UseQueryError';

export const getNotificationBars = (): NotificationBarsType[] => {
    const [{ data, error }] = useNotificationBarsApi();
    useQueryError(error);

    if (data?.notificationBars) {
        return mapNotificationBars(data.notificationBars);
    }
    return [];
};

const mapNotificationBars = (apiData: NotificationBarsFragmentApi[]): NotificationBarsType[] => {
    const mappedNotificationBars = [];

    for (const notificationBarItem of apiData) {
        const { images, ...notificationBarItemData } = notificationBarItem;
        mappedNotificationBars.push({
            ...notificationBarItemData,
            image: getFirstImageSize(images),
        });
    }

    return mappedNotificationBars;
};
