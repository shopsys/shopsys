import { CurrentCustomerUserAuthQueryDocument } from 'graphql/requests/customer/queries/CurrentCustomerUserAuthQuery.generated';
import { CurrentCustomerUserQueryDocument } from 'graphql/requests/customer/queries/CurrentCustomerUserQuery.generated';
import { TypeCustomerUserRoleEnum } from 'graphql/types';
import { Client } from 'urql';
import { getCurrentCustomerUserRoles } from 'utils/auth/getCurrentCustomerUserRoles';
import { describe, expect, test, vi } from 'vitest';

type ReadQueryResult = { data?: unknown } | null | undefined;

const createMockClient = (authResult: ReadQueryResult, fullResult: ReadQueryResult): Client =>
    ({
        readQuery: vi.fn((document: unknown) => {
            if (document === CurrentCustomerUserAuthQueryDocument) {
                return authResult;
            }

            if (document === CurrentCustomerUserQueryDocument) {
                return fullResult;
            }

            return null;
        }),
    }) as unknown as Client;

describe('getCurrentCustomerUserRoles', () => {
    test('returns roles from auth query', () => {
        const roles = [TypeCustomerUserRoleEnum.RoleApiCustomerSelfManage];
        const client = createMockClient(
            {
                data: {
                    currentCustomerUser: {
                        __typename: 'CurrentRegularCustomerUser',
                        uuid: 'user-uuid',
                        roles,
                    },
                },
            },
            undefined,
        );

        expect(getCurrentCustomerUserRoles(client)).toEqual(roles);
    });

    test('returns all roles for guest from auth query', () => {
        const client = createMockClient(
            {
                data: {
                    currentCustomerUser: null,
                },
            },
            undefined,
        );

        expect(getCurrentCustomerUserRoles(client)).toEqual(Object.values(TypeCustomerUserRoleEnum));
    });

    test('falls back to full query roles when auth query is missing', () => {
        const roles = [TypeCustomerUserRoleEnum.RoleApiCartAndOrderCreation];
        const client = createMockClient(undefined, {
            data: {
                currentCustomerUser: {
                    __typename: 'CurrentRegularCustomerUser',
                    uuid: 'full-user-uuid',
                    roles,
                },
            },
        });

        expect(getCurrentCustomerUserRoles(client)).toEqual(roles);
    });

    test('returns all roles for guest from full query fallback', () => {
        const client = createMockClient(undefined, {
            data: {
                currentCustomerUser: null,
            },
        });

        expect(getCurrentCustomerUserRoles(client)).toEqual(Object.values(TypeCustomerUserRoleEnum));
    });

    test('returns empty array when no query data is available', () => {
        const client = createMockClient(undefined, undefined);

        expect(getCurrentCustomerUserRoles(client)).toEqual([]);
    });
});
