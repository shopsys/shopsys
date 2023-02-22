import { NotificationBarsApi, useNotificationBarsApi } from 'graphql/generated';
import { useQueryError } from 'hooks/graphQl/useQueryError';

export const useNotificationBars = (): NotificationBarsApi['notificationBars'] | undefined => {
    const [{ data, error }] = useNotificationBarsApi();
    useQueryError(error);

    return data?.notificationBars;
};
