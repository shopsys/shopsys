import {
    CurrentCustomerUserQueryDocument,
    TypeCurrentCustomerUserQuery,
} from 'graphql/requests/customer/queries/CurrentCustomerUserQuery.generated';
import { TypeCustomerUserRoleEnum } from 'graphql/types';
import { Client } from 'urql';

export const getCurrentCustomerUserRoles = (currentClient: Client): TypeCustomerUserRoleEnum[] => {
    const customerQueryResult = currentClient.readQuery<TypeCurrentCustomerUserQuery>(
        CurrentCustomerUserQueryDocument,
        {},
    );

    if (customerQueryResult?.data?.currentCustomerUser === null) {
        return Object.values(TypeCustomerUserRoleEnum);
    }

    return customerQueryResult?.data?.currentCustomerUser?.roles ?? [];
};
