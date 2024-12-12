import { MenuIconicItemUserAuthenticated } from './MenuIconicItemUserAuthenticated';
import { MenuIconicItemUserUnauthenticated } from './MenuIconicItemUserUnauthenticated';
import { getCurrentCustomerData } from 'app/_queries/getCurrentCustomerData';
import { CurrentCustomerType } from 'types/customer';

export default async function MenuIconicItemUserAuthentication() {
    // await new Promise((resolve) => setTimeout(resolve, 5000));
    const currentCustomerUser: CurrentCustomerType | undefined = await getCurrentCustomerData();

    return currentCustomerUser ? (
        <MenuIconicItemUserAuthenticated currentCustomerUser={currentCustomerUser} />
    ) : (
        <MenuIconicItemUserUnauthenticated />
    );
}
