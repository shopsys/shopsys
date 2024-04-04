import {
    CurrentCustomerUserQuery,
    CurrentCustomerUserQueryDocument,
} from 'graphql/requests/customer/queries/CurrentCustomerUserQuery.generated';
import { Client } from 'urql';

export const isUserLoggedInSSR = (currentClient: Client): boolean => {
    const customerQueryResult = currentClient.readQuery<CurrentCustomerUserQuery>(CurrentCustomerUserQueryDocument, {});

    const isLogged = !!customerQueryResult?.data?.currentCustomerUser;

    return isLogged;
};
