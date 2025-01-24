import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useCurrentCustomerUserQuery } from 'graphql/requests/customer/queries/CurrentCustomerUserQuery.generated';
import { TypeCustomerUserRoleEnum } from 'graphql/types';
import { CustomerUserAreaEnum } from 'types/customer';

export const useUserPermissions = () => {
    const { type } = useDomainConfig();
    const [{ data: currentCustomerUserData, fetching: isCurrentCustomerFetching }] = useCurrentCustomerUserQuery({
        requestPolicy: 'network-only',
    });
    const currentCustomerUser = currentCustomerUserData?.currentCustomerUser;
    const isB2B = type === CustomerUserAreaEnum.B2B;
    const isCompanyUser = isB2B && currentCustomerUser?.__typename === 'CompanyCustomerUser';
    const canManageUsers = isCompanyUser && currentCustomerUser.roles.includes(TypeCustomerUserRoleEnum.RoleApiAll);
    const canManageProfile =
        !currentCustomerUser ||
        !isCompanyUser ||
        currentCustomerUser.roles.includes(TypeCustomerUserRoleEnum.RoleApiAll);

    return {
        currentCustomerUserUuid: currentCustomerUser?.uuid,
        canManageUsers,
        canManageProfile,
        isB2B,
        isCompanyUser,
        isPermissionsFetching: isCurrentCustomerFetching,
    };
};
