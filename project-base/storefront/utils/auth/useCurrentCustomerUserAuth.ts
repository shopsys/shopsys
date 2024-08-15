import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import { CustomerUserRoleEnum } from 'types/customer';

export const useCurrentCustomerUserPermissions = () => {
    const { type } = useDomainConfig();
    const currentCustomerUserData = useCurrentCustomerData();
    const canManageUsers = type === 'b2b' && currentCustomerUserData?.roles.includes(CustomerUserRoleEnum.ROLE_API_ALL);
    const canManageCompanyData =
        type === 'b2b' && currentCustomerUserData?.roles.includes(CustomerUserRoleEnum.ROLE_API_ALL);

    return {
        canManageUsers,
        canManageCompanyData,
    };
};
