import { useDomainConfig } from './DomainConfigProvider';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
import { TypeCustomerUserRoleEnum } from 'graphql/types';
import { createContext, useContext } from 'react';
import { CustomerUserAreaEnum } from 'types/customer';

export const CustomerUserRolesContext = createContext<TypeCustomerUserRoleEnum[] | null>(null);

type AuthorizationProviderProps = {
    customerUserRoles: TypeCustomerUserRoleEnum[];
};

export const AuthorizationProvider: FC<AuthorizationProviderProps> = ({ customerUserRoles, children }) => {
    return <CustomerUserRolesContext.Provider value={customerUserRoles}>{children}</CustomerUserRolesContext.Provider>;
};

export const useAuthorization = () => {
    const { type } = useDomainConfig();
    const customerUserRoles = useContext(CustomerUserRolesContext);

    if (!customerUserRoles) {
        throw new Error(`useAuthorization must be use within AuthorizationProvider`);
    }

    const currentCustomerUser = useCurrentCustomerData();

    const isB2B = type === CustomerUserAreaEnum.B2B;
    const isCompanyUser = isB2B && currentCustomerUser?.companyCustomer;

    const canSeePrices = customerUserRoles.includes(TypeCustomerUserRoleEnum.RoleApiCustomerSeesPrices);
    const canManageUsers = isCompanyUser && customerUserRoles.includes(TypeCustomerUserRoleEnum.RoleApiAll);
    const canManageCompanyData = !isCompanyUser || customerUserRoles.includes(TypeCustomerUserRoleEnum.RoleApiAll);
    const canManagePersonalData = customerUserRoles.includes(TypeCustomerUserRoleEnum.RoleApiCustomerSelfManage);

    const canCreateOrder = isCompanyUser
        ? customerUserRoles.includes(TypeCustomerUserRoleEnum.RoleApiCartAndOrderCreation)
        : true;

    const canViewCompanyOrders = isCompanyUser
        ? customerUserRoles.includes(TypeCustomerUserRoleEnum.RoleApiCompanyOrdersView)
        : true;

    const canCreateComplaint = isCompanyUser
        ? customerUserRoles.includes(TypeCustomerUserRoleEnum.RoleApiComplaintCreation) &&
          (canCreateOrder || canViewCompanyOrders)
        : true;

    const canViewCompanyComplaints = isCompanyUser
        ? customerUserRoles.includes(TypeCustomerUserRoleEnum.RoleApiCompanyComplaintsView)
        : true;

    return {
        currentCustomerUserUuid: currentCustomerUser?.uuid,
        isB2B,
        isCompanyUser,
        canManageUsers,
        canManageCompanyData,
        canManagePersonalData,
        canCreateOrder,
        canViewCompanyOrders,
        canCreateComplaint,
        canViewCompanyComplaints,
        canSeePrices,
    };
};
