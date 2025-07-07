import { TypeCustomerUserRoleEnum } from 'graphql/types';
import { CustomerUserAreaEnum } from 'types/customer';
import { getIsUserAuthorizedToViewPage } from 'utils/auth/getIsUserAuthorizedToViewPage';
import { describe, expect, test } from 'vitest';

describe('getIsUserAuthorizedToViewPage', () => {
    const mockUserRoles: TypeCustomerUserRoleEnum[] = [
        TypeCustomerUserRoleEnum.RoleApiCartAndOrderCreation,
        TypeCustomerUserRoleEnum.RoleApiAll,
    ];

    const mockUserArea = CustomerUserAreaEnum.B2C;

    describe('Role-based authorization', () => {
        test('should return true when user has required role', () => {
            const result = getIsUserAuthorizedToViewPage(
                mockUserRoles,
                mockUserArea,
                [TypeCustomerUserRoleEnum.RoleApiCartAndOrderCreation],
                undefined,
            );

            expect(result).toBe(true);
        });

        test('should return false when user does not have required role', () => {
            const result = getIsUserAuthorizedToViewPage(
                mockUserRoles,
                mockUserArea,
                [TypeCustomerUserRoleEnum.RoleApiCustomerSelfManage],
                undefined,
            );

            expect(result).toBe(false);
        });

        test('should return true when user has any of multiple required roles', () => {
            const result = getIsUserAuthorizedToViewPage(
                mockUserRoles,
                mockUserArea,
                [TypeCustomerUserRoleEnum.RoleApiCustomerSelfManage, TypeCustomerUserRoleEnum.RoleApiAll],
                undefined,
            );

            expect(result).toBe(true);
        });

        test('should return false when user has none of multiple required roles', () => {
            const result = getIsUserAuthorizedToViewPage(
                mockUserRoles,
                mockUserArea,
                [TypeCustomerUserRoleEnum.RoleApiCustomerSelfManage, TypeCustomerUserRoleEnum.RoleApiComplaintCreation],
                undefined,
            );

            expect(result).toBe(false);
        });
    });

    describe('Area-based authorization', () => {
        test('should return true when user is in required area', () => {
            const result = getIsUserAuthorizedToViewPage(mockUserRoles, CustomerUserAreaEnum.B2C, undefined, [
                CustomerUserAreaEnum.B2C,
            ]);

            expect(result).toBe(true);
        });

        test('should return false when user is not in required area', () => {
            const result = getIsUserAuthorizedToViewPage(mockUserRoles, CustomerUserAreaEnum.B2C, undefined, [
                CustomerUserAreaEnum.B2B,
            ]);

            expect(result).toBe(false);
        });

        test('should return true when user is in any of multiple required areas', () => {
            const result = getIsUserAuthorizedToViewPage(mockUserRoles, CustomerUserAreaEnum.B2C, undefined, [
                CustomerUserAreaEnum.B2B,
                CustomerUserAreaEnum.B2C,
            ]);

            expect(result).toBe(true);
        });
    });

    describe('Combined role and area authorization', () => {
        test('should return true when user meets both role and area requirements', () => {
            const result = getIsUserAuthorizedToViewPage(
                mockUserRoles,
                CustomerUserAreaEnum.B2C,
                [TypeCustomerUserRoleEnum.RoleApiCartAndOrderCreation],
                [CustomerUserAreaEnum.B2C],
            );

            expect(result).toBe(true);
        });

        test('should return false when user meets role but not area requirements', () => {
            const result = getIsUserAuthorizedToViewPage(
                mockUserRoles,
                CustomerUserAreaEnum.B2C,
                [TypeCustomerUserRoleEnum.RoleApiCartAndOrderCreation],
                [CustomerUserAreaEnum.B2B],
            );

            expect(result).toBe(false);
        });

        test('should return false when user meets area but not role requirements', () => {
            const result = getIsUserAuthorizedToViewPage(
                mockUserRoles,
                CustomerUserAreaEnum.B2C,
                [TypeCustomerUserRoleEnum.RoleApiCustomerSelfManage],
                [CustomerUserAreaEnum.B2C],
            );

            expect(result).toBe(false);
        });

        test('should return false when user meets neither role nor area requirements', () => {
            const result = getIsUserAuthorizedToViewPage(
                mockUserRoles,
                CustomerUserAreaEnum.B2C,
                [TypeCustomerUserRoleEnum.RoleApiCustomerSelfManage],
                [CustomerUserAreaEnum.B2B],
            );

            expect(result).toBe(false);
        });
    });

    describe('No restrictions (default behavior)', () => {
        test('should return true when no role or area restrictions are specified', () => {
            const result = getIsUserAuthorizedToViewPage(mockUserRoles, mockUserArea, undefined, undefined);

            expect(result).toBe(true);
        });

        test('should return false when empty arrays are provided for restrictions', () => {
            const result = getIsUserAuthorizedToViewPage(mockUserRoles, mockUserArea, [], []);

            expect(result).toBe(false);
        });
    });

    describe('Edge cases', () => {
        test('should handle empty user roles array', () => {
            const result = getIsUserAuthorizedToViewPage(
                [],
                mockUserArea,
                [TypeCustomerUserRoleEnum.RoleApiCartAndOrderCreation],
                undefined,
            );

            expect(result).toBe(false);
        });

        test('should handle empty user roles array with no role restrictions', () => {
            const result = getIsUserAuthorizedToViewPage([], mockUserArea, undefined, [CustomerUserAreaEnum.B2C]);

            expect(result).toBe(true);
        });
    });
});
