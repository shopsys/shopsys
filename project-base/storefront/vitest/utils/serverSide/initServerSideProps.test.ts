import { CurrentCustomerUserAuthQueryDocument } from 'graphql/requests/customer/queries/CurrentCustomerUserAuthQuery.generated';
import { CurrentCustomerUserQueryDocument } from 'graphql/requests/customer/queries/CurrentCustomerUserQuery.generated';
import { TypeCustomerUserRoleEnum } from 'graphql/types';
import { Client, SSRExchange } from 'urql';
import { initServerSideProps } from 'utils/serverSide/initServerSideProps';
import { beforeEach, describe, expect, test, vi } from 'vitest';

const {
    mockIsFullPageRequest,
    mockExtractSeoPageSlugFromUrl,
    mockIsEnvironment,
    mockLoadNamespaces,
    mockGetServerSideInternationalizedStaticUrl,
    mockGetUnauthenticatedRedirectSSR,
} = vi.hoisted(() => ({
    mockIsFullPageRequest: vi.fn(),
    mockExtractSeoPageSlugFromUrl: vi.fn(),
    mockIsEnvironment: vi.fn(),
    mockLoadNamespaces: vi.fn(),
    mockGetServerSideInternationalizedStaticUrl: vi.fn(),
    mockGetUnauthenticatedRedirectSSR: vi.fn(),
}));

vi.mock('utils/isFullPageRequest', () => ({
    isFullPageRequest: mockIsFullPageRequest,
}));

vi.mock('utils/seo/extractSeoPageSlugFromUrl', () => ({
    extractSeoPageSlugFromUrl: mockExtractSeoPageSlugFromUrl,
}));

vi.mock('utils/isEnvironment', () => ({
    isEnvironment: mockIsEnvironment,
}));

vi.mock('next-translate/loadNamespaces', () => ({
    default: mockLoadNamespaces,
}));

vi.mock('utils/staticUrls/getServerSideInternationalizedStaticUrl', () => ({
    getServerSideInternationalizedStaticUrl: mockGetServerSideInternationalizedStaticUrl,
}));

vi.mock('utils/auth/getUnauthenticatedRedirectSSR', () => ({
    getUnauthenticatedRedirectSSR: mockGetUnauthenticatedRedirectSSR,
}));

type ReadQueryResult = { data?: unknown } | null | undefined;
type QueryCall = { query: unknown; variables?: unknown };

const createMockClientWithQueryTracking = (authResult: ReadQueryResult) => {
    const queryCalls: QueryCall[] = [];
    const cacheStore = new Map<unknown, ReadQueryResult>();

    const client = {
        query: vi.fn((requestQuery: unknown, variables?: unknown) => {
            queryCalls.push({ query: requestQuery, variables });

            let data: any = {};
            if (
                requestQuery === CurrentCustomerUserAuthQueryDocument ||
                requestQuery === CurrentCustomerUserQueryDocument
            ) {
                data = authResult?.data ?? {};
                cacheStore.set(requestQuery, { data });
            }

            return {
                toPromise: vi.fn().mockResolvedValue({ data }),
            };
        }),
        readQuery: vi.fn((document: unknown) => {
            return cacheStore.get(document) ?? undefined;
        }),
    } as unknown as Client;

    return { client, queryCalls, cacheStore };
};

const createMockSsrExchange = (): SSRExchange =>
    ({
        extractData: vi.fn().mockReturnValue({}),
    }) as unknown as SSRExchange;

const createContext = (resolvedUrl = '/customer/order-detail') =>
    ({
        resolvedUrl,
        req: { headers: {} },
        res: {
            statusCode: 200,
            setHeader: vi.fn(),
        },
    }) as any;

const domainConfig = {
    url: 'https://example.com',
    defaultLocale: 'en',
    type: 'B2C',
} as any;

describe('initServerSideProps', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        mockIsFullPageRequest.mockReturnValue(false);
        mockExtractSeoPageSlugFromUrl.mockReturnValue(null);
        mockIsEnvironment.mockReturnValue(false);
        mockLoadNamespaces.mockResolvedValue({});
        mockGetUnauthenticatedRedirectSSR.mockReturnValue({
            redirect: { statusCode: 302, destination: '/login?r=customer/order-detail' },
        });
        mockGetServerSideInternationalizedStaticUrl.mockReturnValue({
            trimmedUrlWithoutQueryParams: '/customer/order-detail',
            queryParams: null,
        });
    });

    test('prefetches auth query and makes it available for auth check', async () => {
        const loggedInResult = {
            data: {
                currentCustomerUser: {
                    __typename: 'CurrentRegularCustomerUser',
                    uuid: 'user-uuid',
                    roles: [TypeCustomerUserRoleEnum.RoleApiCartAndOrderCreation],
                },
            },
        };

        const { client } = createMockClientWithQueryTracking(loggedInResult);

        const result = await initServerSideProps({
            context: createContext(),
            client,
            ssrExchange: createMockSsrExchange(),
            domainConfig,
            authenticationConfig: { authenticationRequired: true },
        });

        expect(result).toHaveProperty('props');
    });

    test('redirects to login when user is not logged in and auth is required', async () => {
        const loggedOutResult = {
            data: { currentCustomerUser: null },
        };

        const { client } = createMockClientWithQueryTracking(loggedOutResult);

        const result = await initServerSideProps({
            context: createContext(),
            client,
            ssrExchange: createMockSsrExchange(),
            domainConfig,
            authenticationConfig: { authenticationRequired: true },
        });

        expect(result).toHaveProperty('redirect');
        expect((result as any).redirect.statusCode).toBe(302);
    });

    test('prefetches full customer query when full mode is specified', async () => {
        const loggedInResult = {
            data: {
                currentCustomerUser: {
                    __typename: 'CurrentRegularCustomerUser',
                    uuid: 'user-uuid',
                    roles: [TypeCustomerUserRoleEnum.RoleApiCartAndOrderCreation],
                },
            },
        };

        const { client, queryCalls } = createMockClientWithQueryTracking(loggedInResult);

        await initServerSideProps({
            context: createContext(),
            client,
            ssrExchange: createMockSsrExchange(),
            domainConfig,
            currentCustomerUserPrefetchMode: 'full',
        });

        expect(queryCalls[0].query).toBe(CurrentCustomerUserQueryDocument);
    });

    test('prefetches auth query by default', async () => {
        const loggedInResult = {
            data: {
                currentCustomerUser: {
                    __typename: 'CurrentRegularCustomerUser',
                    uuid: 'user-uuid',
                    roles: [],
                },
            },
        };

        const { client, queryCalls } = createMockClientWithQueryTracking(loggedInResult);

        await initServerSideProps({
            context: createContext(),
            client,
            ssrExchange: createMockSsrExchange(),
            domainConfig,
        });

        expect(queryCalls[0].query).toBe(CurrentCustomerUserAuthQueryDocument);
    });

    test('passes additional prefetched queries to prefetchLayoutQueries', async () => {
        const loggedInResult = {
            data: {
                currentCustomerUser: {
                    __typename: 'CurrentRegularCustomerUser',
                    uuid: 'user-uuid',
                    roles: [],
                },
            },
        };

        const additionalQuery = { query: 'CustomQuery' as any, variables: { id: '123' } };
        const { client, queryCalls } = createMockClientWithQueryTracking(loggedInResult);

        await initServerSideProps({
            context: createContext(),
            client,
            ssrExchange: createMockSsrExchange(),
            domainConfig,
            prefetchedQueries: [additionalQuery],
        });

        expect(queryCalls).toContainEqual({ query: 'CustomQuery', variables: { id: '123' } });
    });

    test('merges additionalProps into the result', async () => {
        const loggedInResult = {
            data: {
                currentCustomerUser: {
                    __typename: 'CurrentRegularCustomerUser',
                    uuid: 'user-uuid',
                    roles: [],
                },
            },
        };

        const { client } = createMockClientWithQueryTracking(loggedInResult);

        const result = await initServerSideProps({
            context: createContext(),
            client,
            ssrExchange: createMockSsrExchange(),
            domainConfig,
            additionalProps: { orderNumber: '12345' },
        });

        expect((result as any).props.orderNumber).toBe('12345');
    });
});
