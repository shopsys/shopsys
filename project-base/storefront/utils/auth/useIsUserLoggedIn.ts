import { useCurrentCustomerUserQuery } from 'graphql/requests/customer/queries/CurrentCustomerUserQuery.generated';

export const useIsUserLoggedIn = (): boolean => {
    const [{ data: currentCustomerUserData }] = useCurrentCustomerUserQuery();

    return !!currentCustomerUserData?.currentCustomerUser;
};
