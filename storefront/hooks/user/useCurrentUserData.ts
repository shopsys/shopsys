import { CurrentCustomerType } from 'types/customer';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';

export const useCurrentUserData = (): { user: CurrentCustomerType | undefined; isUserLoggedIn: boolean } => {
    const data = useCurrentCustomerData();

    return { user: data, isUserLoggedIn: !!data };
};
