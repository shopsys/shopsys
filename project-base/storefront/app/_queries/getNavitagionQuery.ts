'use server';

import { createQuery } from 'app/_urql/urql-dto';
import {
    TypeNavigationQuery,
    TypeNavigationQueryVariables,
    NavigationQueryDocument,
} from 'graphql/requests/navigation/queries/NavigationQuery.ssr';

export default async function getNavitagionQuery() {
    const navigationResponse = await createQuery<TypeNavigationQuery, TypeNavigationQueryVariables>(
        NavigationQueryDocument,
        {},
    );

    return navigationResponse.data;
}
