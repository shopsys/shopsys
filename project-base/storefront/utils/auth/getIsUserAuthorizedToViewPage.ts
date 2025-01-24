import { TypeCustomerUserRoleEnum } from 'graphql/types';
import { CustomerUserAreaEnum } from 'types/customer';

export const getIsUserAuthorizedToViewPage = (
    currentCustomerUserRoles: TypeCustomerUserRoleEnum[],
    currentCustomerUserArea: CustomerUserAreaEnum,
    allowedUserRoles?: TypeCustomerUserRoleEnum[],
    allowedUserAreas?: CustomerUserAreaEnum[],
): boolean => {
    const isAreaAllowed = allowedUserAreas?.includes(currentCustomerUserArea) ?? true;
    const isRoleAllowed = allowedUserRoles?.some((role) => currentCustomerUserRoles.includes(role)) ?? true;

    return isAreaAllowed && isRoleAllowed;
};
