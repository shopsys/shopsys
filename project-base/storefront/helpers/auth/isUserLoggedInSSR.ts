import { CurrentCustomerUserQueryApi, CurrentCustomerUserQueryDocumentApi } from 'graphql/generated';
import { Client } from 'urql';

export const isUserLoggedInSSR = (currentClient: Client): boolean => {
    const customerQueryResult = currentClient.readQuery<CurrentCustomerUserQueryApi>(
        CurrentCustomerUserQueryDocumentApi,
        {},
    );

    const isLogged = !!customerQueryResult?.data?.currentCustomerUser;

    return isLogged;
};
