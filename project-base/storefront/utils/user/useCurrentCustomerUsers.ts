import { useRedirectOnPermissionsChange } from './useRedirectOnPermissionsChange';
import { useCurrentCustomerUsersQuery } from 'graphql/requests/customer/queries/CurrentCustomerUsersQuery.generated';

export const useCurrentCustomerUsers = () => {
    const [{ data: currentCustomerUsersData, error }] = useCurrentCustomerUsersQuery({
        requestPolicy: 'network-only',
    });
    const { redirect } = useRedirectOnPermissionsChange();

    if (error?.networkError && error.networkError.message.includes('No Content')) {
        redirect();
    }

    return currentCustomerUsersData?.customerUsers ?? [];
};
