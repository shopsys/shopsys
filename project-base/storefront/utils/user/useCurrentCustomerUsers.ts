import { useRedirectOnPermissionsChange } from './useRedirectOnPermissionsChange';
import { useCurrentCustomerUsersQuery } from 'graphql/requests/customer/queries/CurrentCustomerUsersQuery.generated';

export const useCurrentCustomerUsers = () => {
    const [{ data: currentCustomerUsersData, error, fetching }] = useCurrentCustomerUsersQuery({
        requestPolicy: 'network-only',
    });
    const { redirect } = useRedirectOnPermissionsChange();

    if (error?.networkError && error.networkError.message.includes('No Content')) {
        redirect();
    }

    return {
        customerUsers: currentCustomerUsersData?.customerUsers ?? [],
        customerUsersIsFetching: fetching,
    };
};
