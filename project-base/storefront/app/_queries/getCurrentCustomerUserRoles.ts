'use server';

import { readQuery } from 'app/_urql/urql-dto';
import {
    CurrentCustomerUserQueryDocument,
    TypeCurrentCustomerUserQuery,
} from 'graphql/requests/customer/queries/CurrentCustomerUserQuery.ssr';
import { TypeCustomerUserRoleEnum } from 'graphql/types';

export const getCurrentCustomerUserRoles = async (): Promise<TypeCustomerUserRoleEnum[]> => {
    const customerQueryResult = await readQuery<TypeCurrentCustomerUserQuery>(CurrentCustomerUserQueryDocument, {});

    if (customerQueryResult.data?.currentCustomerUser === null) {
        return Object.values(TypeCustomerUserRoleEnum);
    }

    return customerQueryResult.data?.currentCustomerUser?.roles ?? [];
};
