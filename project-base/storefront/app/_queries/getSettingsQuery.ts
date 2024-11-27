'use server';

import { createQuery } from 'app/_urql/urql-dto';
import {
    SettingsQueryDocument,
    TypeSettingsQuery,
    TypeSettingsQueryVariables,
} from 'graphql/requests/settings/queries/SettingsQuery.ssr';

export async function getSettingsQuery() {
    const result = await createQuery<TypeSettingsQuery, TypeSettingsQueryVariables>(SettingsQueryDocument, {});

    return result;
}
