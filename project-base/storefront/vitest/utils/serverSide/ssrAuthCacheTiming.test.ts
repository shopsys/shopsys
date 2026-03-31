import { CurrentCustomerUserAuthQueryDocument } from 'graphql/requests/customer/queries/CurrentCustomerUserAuthQuery.generated';
import { CurrentCustomerUserQueryDocument } from 'graphql/requests/customer/queries/CurrentCustomerUserQuery.generated';
import { TypeCustomerUserRoleEnum } from 'graphql/types';
import { Client } from 'urql';
import { getCurrentCustomerUserRoles } from 'utils/auth/getCurrentCustomerUserRoles';
import { isUserLoggedInSSR } from 'utils/auth/isUserLoggedInSSR';
import { describe, expect, test, vi } from 'vitest';

/**
 * These tests verify the interaction between SSR cache population and auth checks.
 * They cover the bug pattern where isUserLoggedInSSR() is called before the auth
 * query has been prefetched, causing it to return false for logged-in users.
 *
 * See: order-detail.tsx getServerSideProps — isUserLoggedInSSR was called before
 * initServerSideProps, so the cache was empty and auth check always returned false.
 */

type CacheEntry = { data?: unknown };

const createClientWithCache = () => {
    const cache = new Map<unknown, CacheEntry>();

    const client = {
        query: vi.fn((document: unknown) => ({
            toPromise: vi.fn().mockImplementation(async () => {
                const entry = cache.get(document);
                if (entry) {
                    return entry;
                }

                return { data: null };
            }),
        })),
        readQuery: vi.fn((document: unknown) => {
            return cache.get(document) ?? undefined;
        }),
    } as unknown as Client;

    const populateCache = (document: unknown, data: CacheEntry) => {
        cache.set(document, data);
    };

    return { client, populateCache };
};

const loggedInAuthData: CacheEntry = {
    data: {
        currentCustomerUser: {
            __typename: 'CurrentRegularCustomerUser',
            uuid: 'user-uuid',
            roles: [TypeCustomerUserRoleEnum.RoleApiCartAndOrderCreation],
        },
    },
};

const loggedInFullData: CacheEntry = {
    data: {
        currentCustomerUser: {
            __typename: 'CurrentRegularCustomerUser',
            uuid: 'user-uuid',
            firstName: 'John',
            lastName: 'Doe',
            roles: [TypeCustomerUserRoleEnum.RoleApiCartAndOrderCreation],
        },
    },
};

const loggedOutData: CacheEntry = {
    data: {
        currentCustomerUser: null,
    },
};

describe('SSR auth cache timing', () => {
    describe('isUserLoggedInSSR on cold cache', () => {
        test('returns false when cache is empty — even for authenticated users', () => {
            const { client } = createClientWithCache();

            // No query has been fetched yet — cache is cold
            expect(isUserLoggedInSSR(client)).toBe(false);
        });

        test('returns true after auth query is populated in cache', () => {
            const { client, populateCache } = createClientWithCache();

            // Simulate prefetch populating the cache
            populateCache(CurrentCustomerUserAuthQueryDocument, loggedInAuthData);

            expect(isUserLoggedInSSR(client)).toBe(true);
        });

        test('returns true after full query is populated in cache', () => {
            const { client, populateCache } = createClientWithCache();

            // Full mode prefetch
            populateCache(CurrentCustomerUserQueryDocument, loggedInFullData);

            expect(isUserLoggedInSSR(client)).toBe(true);
        });

        test('returns false after auth query is populated with null user (guest)', () => {
            const { client, populateCache } = createClientWithCache();

            populateCache(CurrentCustomerUserAuthQueryDocument, loggedOutData);

            expect(isUserLoggedInSSR(client)).toBe(false);
        });
    });

    describe('getCurrentCustomerUserRoles on cold cache', () => {
        test('returns empty array when cache is empty', () => {
            const { client } = createClientWithCache();

            expect(getCurrentCustomerUserRoles(client)).toEqual([]);
        });

        test('returns roles after auth query is populated', () => {
            const { client, populateCache } = createClientWithCache();

            populateCache(CurrentCustomerUserAuthQueryDocument, loggedInAuthData);

            expect(getCurrentCustomerUserRoles(client)).toEqual([TypeCustomerUserRoleEnum.RoleApiCartAndOrderCreation]);
        });

        test('returns all roles for guest after cache populated with null user', () => {
            const { client, populateCache } = createClientWithCache();

            populateCache(CurrentCustomerUserAuthQueryDocument, loggedOutData);

            expect(getCurrentCustomerUserRoles(client)).toEqual(Object.values(TypeCustomerUserRoleEnum));
        });
    });

    describe('prefetch-before-check pattern (the fix for the bug)', () => {
        test('manual query fetch before isUserLoggedInSSR populates cache correctly', async () => {
            const { client, populateCache } = createClientWithCache();

            // Step 1: cache is cold — auth check would fail
            expect(isUserLoggedInSSR(client)).toBe(false);

            // Step 2: manually prefetch the full query (simulating the fix)
            populateCache(CurrentCustomerUserQueryDocument, loggedInFullData);

            // Step 3: now auth check succeeds
            expect(isUserLoggedInSSR(client)).toBe(true);
        });

        test('auth query takes priority over full query when both are cached', () => {
            const { client, populateCache } = createClientWithCache();

            // Both queries in cache — auth query should be checked first
            populateCache(CurrentCustomerUserAuthQueryDocument, loggedOutData);
            populateCache(CurrentCustomerUserQueryDocument, loggedInFullData);

            // Auth query says "not logged in" (null user), so result should be false
            // even though full query has user data
            expect(isUserLoggedInSSR(client)).toBe(false);
        });
    });

    describe('cache consistency between auth check and role extraction', () => {
        test('isUserLoggedInSSR and getCurrentCustomerUserRoles agree on logged-in state', () => {
            const { client, populateCache } = createClientWithCache();
            populateCache(CurrentCustomerUserAuthQueryDocument, loggedInAuthData);

            const isLoggedIn = isUserLoggedInSSR(client);
            const roles = getCurrentCustomerUserRoles(client);

            expect(isLoggedIn).toBe(true);
            expect(roles.length).toBeGreaterThan(0);
        });

        test('isUserLoggedInSSR and getCurrentCustomerUserRoles agree on logged-out state', () => {
            const { client, populateCache } = createClientWithCache();
            populateCache(CurrentCustomerUserAuthQueryDocument, loggedOutData);

            const isLoggedIn = isUserLoggedInSSR(client);
            const roles = getCurrentCustomerUserRoles(client);

            expect(isLoggedIn).toBe(false);
            // Guest gets all roles (by design)
            expect(roles).toEqual(Object.values(TypeCustomerUserRoleEnum));
        });

        test('both functions return consistent "empty" state on cold cache', () => {
            const { client } = createClientWithCache();

            const isLoggedIn = isUserLoggedInSSR(client);
            const roles = getCurrentCustomerUserRoles(client);

            // Both return "no data" defaults
            expect(isLoggedIn).toBe(false);
            expect(roles).toEqual([]);
        });
    });
});
