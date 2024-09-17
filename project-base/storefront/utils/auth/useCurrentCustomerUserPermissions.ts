import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useCurrentCustomerUserQuery } from 'graphql/requests/customer/queries/CurrentCustomerUserQuery.generated';
import { CustomerUserRoleEnum } from 'types/customer';

export const useCurrentCustomerUserPermissions = () => {
    const { type } = useDomainConfig();
    const [{ data: currentCustomerUserData }] = useCurrentCustomerUserQuery({
        requestPolicy: 'network-only',
    });
    const currentCustomerUser = currentCustomerUserData?.currentCustomerUser;
    const isCompanyUser = type === 'B2B';
    const canManageUsers = isCompanyUser && currentCustomerUser?.roles.includes(CustomerUserRoleEnum.ROLE_API_ALL);
    const canManageProfile =
        !currentCustomerUser || !isCompanyUser || currentCustomerUser.roles.includes(CustomerUserRoleEnum.ROLE_API_ALL);

    return {
        currentCustomerUserUuid: currentCustomerUser?.uuid,
        canManageUsers,
        canManageProfile,
    };
};
