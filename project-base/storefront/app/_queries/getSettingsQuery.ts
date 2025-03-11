'use server';

import { createQuery } from 'app/_urql/urql-dto';
import {
    SettingsQueryDocument,
    TypeSettingsQuery,
    TypeSettingsQueryVariables,
} from 'graphql/requests/settings/queries/SettingsQuery.ssr';

export const getSettingsQuery = async () => {
    const result = await createQuery<TypeSettingsQuery, TypeSettingsQueryVariables>(SettingsQueryDocument, {});

    return { data: result.data, error: result.error };
};
