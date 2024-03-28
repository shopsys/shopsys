import {
    TypeCurrentCustomerUserQuery,
    CurrentCustomerUserQueryDocument,
} from 'graphql/requests/customer/queries/CurrentCustomerUserQuery.generated';
import { Client } from 'urql';

export const isUserLoggedInSSR = (currentClient: Client): boolean => {
    const customerQueryResult = currentClient.readQuery<TypeCurrentCustomerUserQuery>(
        CurrentCustomerUserQueryDocument,
        {},
    );

    const isLogged = !!customerQueryResult?.data?.currentCustomerUser;

    return isLogged;
};
