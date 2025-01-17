import { MenuIconicItemUserAuthenticated } from './MenuIconicItemUserAuthenticated';
import { MenuIconicItemUserUnauthenticated } from './MenuIconicItemUserUnauthenticated';
import { getCurrentCustomerData } from 'app/_queries/getCurrentCustomerData';
import { CurrentCustomerType } from 'types/customer';

export const MenuIconicItemUserAuthentication = async () => {
    const currentCustomerUser: CurrentCustomerType | undefined = await getCurrentCustomerData();

    return currentCustomerUser ? (
        <MenuIconicItemUserAuthenticated currentCustomerUser={currentCustomerUser} />
    ) : (
        <MenuIconicItemUserUnauthenticated />
    );
};
