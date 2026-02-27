import { useCurrentCustomerUserQueryData } from 'components/providers/CurrentCustomerUserProvider';

export const useIsUserLoggedIn = (): boolean => {
    const currentCustomerUserData = useCurrentCustomerUserQueryData();

    return !!currentCustomerUserData?.currentCustomerUser;
};
