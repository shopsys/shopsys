'use server';

import { createQuery } from 'app/_urql/urql-dto';
import {
    NotificationBarsDocument,
    TypeNotificationBars,
    TypeNotificationBarsVariables,
} from 'graphql/requests/notificationBars/queries/NotificationBarsQuery.ssr';

export const getNotificationBarsQuery = async () => {
    const result = await createQuery<TypeNotificationBars, TypeNotificationBarsVariables>(NotificationBarsDocument, {});

    return result;
};
