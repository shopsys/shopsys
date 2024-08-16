import { CustomerUserAreaEnum, CustomerUserRoleEnum } from 'types/customer';

export const getIsUserAuthorizedToViewPage = (
    currentCustomerUserRoles: string[],
    currentCustomerUserArea: CustomerUserAreaEnum,
    allowedUserRoles?: CustomerUserRoleEnum[],
    allowedUserAreas?: CustomerUserAreaEnum[],
): boolean => {
    const isAreaAllowed = allowedUserAreas?.includes(currentCustomerUserArea) ?? true;
    const isRoleAllowed = allowedUserRoles?.some((role) => currentCustomerUserRoles.includes(role)) ?? true;

    return isAreaAllowed && isRoleAllowed;
};
