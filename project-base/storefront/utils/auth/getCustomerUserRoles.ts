import {
  TypeCurrentCustomerUserQuery,
  CurrentCustomerUserQueryDocument,
} from 'graphql/requests/customer/queries/CurrentCustomerUserQuery.generated';
import { Client } from 'urql';

export const getCustomerUserRoles = (currentClient: Client): string[] => {
  const customerQueryResult = currentClient.readQuery<TypeCurrentCustomerUserQuery>(
      CurrentCustomerUserQueryDocument,
      {},
  );

  return customerQueryResult?.data?.currentCustomerUser?.roles ?? [];
};
