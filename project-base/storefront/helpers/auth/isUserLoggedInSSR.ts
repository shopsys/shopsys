import {
    CurrentCustomerUserQueryApi,
    CurrentCustomerUserQueryDocumentApi,
} from 'graphql/requests/customer/queries/CurrentCustomerUserQuery.generated';
import { Client } from 'urql';

export const isUserLoggedInSSR = (currentClient: Client): boolean => {
    const customerQueryResult = currentClient.readQuery<CurrentCustomerUserQueryApi>(
        CurrentCustomerUserQueryDocumentApi,
        {},
    );

    const isLogged =
        customerQueryResult?.data?.currentCustomerUser !== undefined &&
        // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition
        customerQueryResult?.data?.currentCustomerUser !== null;

    return isLogged;
};
