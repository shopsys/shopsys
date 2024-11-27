'use server';

import { createQuery } from 'app/_urql/urql-dto';
import {
    CurrentCustomerUserQueryDocument,
    TypeCurrentCustomerUserQuery,
    TypeCurrentCustomerUserQueryVariables,
} from 'graphql/requests/customer/queries/CurrentCustomerUserQuery.ssr';

export async function getIsUserLoggedInQuery() {
    const result = await createQuery<TypeCurrentCustomerUserQuery, TypeCurrentCustomerUserQueryVariables>(
        CurrentCustomerUserQueryDocument,
        {},
    );

    return !!result.data?.currentCustomerUser;
}
