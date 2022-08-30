import { getFirstImage } from 'connectors/image/Image';
import { NotificationBarsFragmentApi, useNotificationBarsApi } from 'graphql/generated';
import { useQueryError } from 'hooks/graphQl/useQueryError';
import { NotificationBarsType } from 'types/notificationBars';

export const useNotificationBars = (): NotificationBarsType[] => {
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
            image: getFirstImage(images),
        });
    }

    return mappedNotificationBars;
};
