import {
    CurrentCustomerUserAuthQueryDocument,
    TypeCurrentCustomerUserAuthQuery,
} from 'graphql/requests/customer/queries/CurrentCustomerUserAuthQuery.generated';
import {
    CurrentCustomerUserQueryDocument,
    TypeCurrentCustomerUserQuery,
} from 'graphql/requests/customer/queries/CurrentCustomerUserQuery.generated';
import { TypeCustomerUserRoleEnum } from 'graphql/types';
import { Client } from 'urql';

export const getCurrentCustomerUserRoles = (currentClient: Client): TypeCustomerUserRoleEnum[] => {
    const authCustomerQueryResult = currentClient.readQuery<TypeCurrentCustomerUserAuthQuery>(
        CurrentCustomerUserAuthQueryDocument,
        {},
    );
    if (authCustomerQueryResult?.data?.currentCustomerUser !== undefined) {
        if (authCustomerQueryResult.data.currentCustomerUser === null) {
            return Object.values(TypeCustomerUserRoleEnum);
        }

        return authCustomerQueryResult.data.currentCustomerUser.roles;
    }

    const fullCustomerQueryResult = currentClient.readQuery<TypeCurrentCustomerUserQuery>(
        CurrentCustomerUserQueryDocument,
        {},
    );

    if (fullCustomerQueryResult?.data?.currentCustomerUser === null) {
        return Object.values(TypeCustomerUserRoleEnum);
    }

    return fullCustomerQueryResult?.data?.currentCustomerUser?.roles ?? [];
};
