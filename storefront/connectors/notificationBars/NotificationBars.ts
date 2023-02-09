import { NotificationBarsFragmentApi, useNotificationBarsApi } from 'graphql/generated';
import { useQueryError } from 'hooks/graphQl/useQueryError';

export const useNotificationBars = (): NotificationBarsFragmentApi[] | undefined => {
    const [{ data, error }] = useNotificationBarsApi();
    useQueryError(error);

    return data?.notificationBars ?? undefined;
};
