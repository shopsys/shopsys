import { useCurrentCustomerUserQueryApi } from 'graphql/generated';

export const useIsUserLoggedIn = (): boolean => {
    const [{ data }] = useCurrentCustomerUserQueryApi();

    return !!data?.currentCustomerUser;
};
