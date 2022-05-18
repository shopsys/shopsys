import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import { CurrentCustomerType } from 'types/customer';

export const useCurrentUserData = (): { user: CurrentCustomerType | undefined; isUserLoggedIn: boolean } => {
    const data = useCurrentCustomerData();

    return { user: data, isUserLoggedIn: !!data };
};
