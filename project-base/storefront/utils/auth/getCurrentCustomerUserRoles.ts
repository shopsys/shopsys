import {
    TypeCurrentCustomerUserQuery,
    CurrentCustomerUserQueryDocument,
} from 'graphql/requests/customer/queries/CurrentCustomerUserQuery.generated';
import { TypeCustomerUserRoleEnum } from 'graphql/types';
import { Client } from 'urql';

export const getCurrentCustomerUserRoles = (currentClient: Client): TypeCustomerUserRoleEnum[] => {
    const customerQueryResult = currentClient.readQuery<TypeCurrentCustomerUserQuery>(
        CurrentCustomerUserQueryDocument,
        {},
    );

    return customerQueryResult?.data?.currentCustomerUser?.roles ?? [];
};
