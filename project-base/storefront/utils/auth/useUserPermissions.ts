import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useCurrentCustomerUserQuery } from 'graphql/requests/customer/queries/CurrentCustomerUserQuery.generated';
import { CustomerUserAreaEnum, CustomerUserRoleEnum } from 'types/customer';

export const useUserPermissions = () => {
    const { type } = useDomainConfig();
    const [{ data: currentCustomerUserData }] = useCurrentCustomerUserQuery({
        requestPolicy: 'network-only',
    });
    const currentCustomerUser = currentCustomerUserData?.currentCustomerUser;
    const isB2B = type === CustomerUserAreaEnum.B2B;
    const isCompanyUser = isB2B && currentCustomerUser?.__typename === 'CompanyCustomerUser';
    const canManageUsers = isCompanyUser && currentCustomerUser.roles.includes(CustomerUserRoleEnum.ROLE_API_ALL);
    const canManageProfile =
        !currentCustomerUser || !isCompanyUser || currentCustomerUser.roles.includes(CustomerUserRoleEnum.ROLE_API_ALL);

    return {
        currentCustomerUserUuid: currentCustomerUser?.uuid,
        canManageUsers,
        canManageProfile,
        isB2B,
        isCompanyUser,
    };
};
