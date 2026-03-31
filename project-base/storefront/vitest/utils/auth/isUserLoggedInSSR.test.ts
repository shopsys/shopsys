import { CurrentCustomerUserAuthQueryDocument } from 'graphql/requests/customer/queries/CurrentCustomerUserAuthQuery.generated';
import { CurrentCustomerUserQueryDocument } from 'graphql/requests/customer/queries/CurrentCustomerUserQuery.generated';
import { Client } from 'urql';
import { isUserLoggedInSSR } from 'utils/auth/isUserLoggedInSSR';
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

describe('isUserLoggedInSSR', () => {
    test('returns true when auth query contains user', () => {
        const client = createMockClient(
            {
                data: {
                    currentCustomerUser: {
                        __typename: 'CurrentRegularCustomerUser',
                        uuid: 'user-uuid',
                        roles: [],
                    },
                },
            },
            undefined,
        );

        expect(isUserLoggedInSSR(client)).toBe(true);
    });

    test('returns false when auth query returns null user', () => {
        const client = createMockClient(
            {
                data: {
                    currentCustomerUser: null,
                },
            },
            undefined,
        );

        expect(isUserLoggedInSSR(client)).toBe(false);
    });

    test('falls back to full query when auth query is missing', () => {
        const client = createMockClient(undefined, {
            data: {
                currentCustomerUser: {
                    __typename: 'CurrentRegularCustomerUser',
                    uuid: 'full-user-uuid',
                    roles: [],
                },
            },
        });

        expect(isUserLoggedInSSR(client)).toBe(true);
    });

    test('returns false when neither auth nor full query has user data', () => {
        const client = createMockClient(undefined, undefined);

        expect(isUserLoggedInSSR(client)).toBe(false);
    });
});
