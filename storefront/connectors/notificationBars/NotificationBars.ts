import { useNotificationBarsApi } from 'graphql/generated';
import { useQueryError } from 'hooks/graphQl/useQueryError';
import { NotificationBarsType } from 'types/notificationBars';

export const useNotificationBars = (): NotificationBarsType[] => {
    const [{ data, error }] = useNotificationBarsApi();
    useQueryError(error);

    if (data?.notificationBars) {
        return data.notificationBars;
    }

    return [];
};
