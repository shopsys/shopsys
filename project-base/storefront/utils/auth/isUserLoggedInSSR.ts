import {
    CurrentCustomerUserAuthQueryDocument,
    TypeCurrentCustomerUserAuthQuery,
} from 'graphql/requests/customer/queries/CurrentCustomerUserAuthQuery.generated';
import {
    CurrentCustomerUserQueryDocument,
    TypeCurrentCustomerUserQuery,
} from 'graphql/requests/customer/queries/CurrentCustomerUserQuery.generated';
import { Client } from 'urql';

export const isUserLoggedInSSR = (currentClient: Client): boolean => {
    const authCustomerQueryResult = currentClient.readQuery<TypeCurrentCustomerUserAuthQuery>(
        CurrentCustomerUserAuthQueryDocument,
        {},
    );
    if (authCustomerQueryResult?.data?.currentCustomerUser !== undefined) {
        return !!authCustomerQueryResult.data.currentCustomerUser;
    }

    const fullCustomerQueryResult = currentClient.readQuery<TypeCurrentCustomerUserQuery>(
        CurrentCustomerUserQueryDocument,
        {},
    );

    const isLogged = !!fullCustomerQueryResult?.data?.currentCustomerUser;

    return isLogged;
};
