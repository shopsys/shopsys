import { useDomainConfig } from './DomainConfigProvider';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import { TypeCustomerUserRoleEnum } from 'graphql/types';
import { createContext, useContext } from 'react';
import { CustomerUserAreaEnum } from 'types/customer';

export const CustomerUserRolesContext = createContext<TypeCustomerUserRoleEnum[]>([]);

type AuthorizationProviderProps = {
    customerUserRoles: TypeCustomerUserRoleEnum[];
};

export const AuthorizationProvider: FC<AuthorizationProviderProps> = ({ customerUserRoles, children }) => {
    return <CustomerUserRolesContext.Provider value={customerUserRoles}>{children}</CustomerUserRolesContext.Provider>;
};

export const useAuthorization = () => {
    const { type } = useDomainConfig();
    const customerUserRoles = useContext(CustomerUserRolesContext);
    const currentCustomerUser = useCurrentCustomerData();

    const isB2B = type === CustomerUserAreaEnum.B2B;
    const isCompanyUser = isB2B && currentCustomerUser?.companyCustomer;

    const canManageUsers = isCompanyUser && customerUserRoles.includes(TypeCustomerUserRoleEnum.RoleApiAll);
    const canManageProfile = !isCompanyUser || customerUserRoles.includes(TypeCustomerUserRoleEnum.RoleApiAll);

    const canCreateOrder = isCompanyUser
        ? customerUserRoles.includes(TypeCustomerUserRoleEnum.RoleApiCartAndOrderCreation)
        : true;

    const canViewCompanyOrders = isCompanyUser
        ? customerUserRoles.includes(TypeCustomerUserRoleEnum.RoleApiCompanyOrdersView)
        : true;

    return {
        currentCustomerUserUuid: currentCustomerUser?.uuid,
        isB2B,
        isCompanyUser,
        canManageUsers,
        canManageProfile,
        canCreateOrder,
        canViewCompanyOrders,
    };
};
