import { useCurrentCustomerUsersQuery } from 'graphql/requests/customer/queries/CurrentCustomerUsersQuery.generated';

export const useCurrentCustomerUsers = () => {
    const [{ data: currentCustomerUsersData }] = useCurrentCustomerUsersQuery();
    return currentCustomerUsersData?.customerUsers ?? [];
};
