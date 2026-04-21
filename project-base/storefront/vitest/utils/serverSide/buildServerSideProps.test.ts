import { CurrentCustomerUserAuthQueryDocument } from 'graphql/requests/customer/queries/CurrentCustomerUserAuthQuery.generated';
import { CurrentCustomerUserQueryDocument } from 'graphql/requests/customer/queries/CurrentCustomerUserQuery.generated';
import { TypeCustomerUserRoleEnum } from 'graphql/types';
import { CustomerUserAreaEnum } from 'types/customer';
import { Client, SSRExchange } from 'urql';
import { buildServerSideProps } from 'utils/serverSide/buildServerSideProps';
import { LayoutQueryResult } from 'utils/serverSide/types';
import { beforeEach, describe, expect, test, vi } from 'vitest';

const {
    mockIsEnvironment,
    mockLoadNamespaces,
    mockGetServerSideInternationalizedStaticUrl,
    mockGetUnauthenticatedRedirectSSR,
} = vi.hoisted(() => ({
    mockIsEnvironment: vi.fn(),
    mockLoadNamespaces: vi.fn(),
    mockGetServerSideInternationalizedStaticUrl: vi.fn(),
    mockGetUnauthenticatedRedirectSSR: vi.fn(),
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

const createMockSsrExchange = (): SSRExchange =>
    ({
        extractData: vi.fn().mockReturnValue({}),
    }) as unknown as SSRExchange;

const createMockContext = (resolvedUrl = '/customer/order-detail') =>
    ({
        resolvedUrl,
        res: {
            statusCode: 200,
            setHeader: vi.fn(),
        },
    }) as any;

const domainConfig = {
    url: 'https://example.com',
    defaultLocale: 'en',
    type: CustomerUserAreaEnum.B2C,
} as any;

const createLayoutResult = (resolvedQueries: any[] = []): LayoutQueryResult => ({
    resolvedQueries,
    seoPageSlug: null,
});

const loggedInUserAuthResult = {
    data: {
        currentCustomerUser: {
            __typename: 'CurrentRegularCustomerUser',
            uuid: 'user-uuid',
            roles: [TypeCustomerUserRoleEnum.RoleApiCartAndOrderCreation],
        },
    },
};

const loggedOutAuthResult = {
    data: {
        currentCustomerUser: null,
    },
};

describe('buildServerSideProps', () => {
    beforeEach(() => {
        vi.clearAllMocks();
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

    describe('authentication', () => {
        test('returns props when auth not required', async () => {
            const client = createMockClient(undefined, undefined);
            const result = await buildServerSideProps({
                layoutResult: createLayoutResult(),
                client,
                ssrExchange: createMockSsrExchange(),
                context: createMockContext(),
                domainConfig,
                authenticationConfig: { authenticationRequired: false },
            });

            expect(result).toHaveProperty('props');
        });

        test('returns props when auth required and user is logged in', async () => {
            const client = createMockClient(loggedInUserAuthResult, undefined);
            const result = await buildServerSideProps({
                layoutResult: createLayoutResult(),
                client,
                ssrExchange: createMockSsrExchange(),
                context: createMockContext(),
                domainConfig,
                authenticationConfig: { authenticationRequired: true },
            });

            expect(result).toHaveProperty('props');
        });

        test('returns 302 redirect to login when auth required and user is not logged in', async () => {
            const client = createMockClient(loggedOutAuthResult, undefined);
            const result = await buildServerSideProps({
                layoutResult: createLayoutResult(),
                client,
                ssrExchange: createMockSsrExchange(),
                context: createMockContext(),
                domainConfig,
                authenticationConfig: { authenticationRequired: true },
            });

            expect(result).toHaveProperty('redirect');
            expect((result as any).redirect.statusCode).toBe(302);
        });

        test('returns 302 redirect when auth required and cache is empty (cold SSR)', async () => {
            const client = createMockClient(undefined, undefined);
            const result = await buildServerSideProps({
                layoutResult: createLayoutResult(),
                client,
                ssrExchange: createMockSsrExchange(),
                context: createMockContext(),
                domainConfig,
                authenticationConfig: { authenticationRequired: true },
            });

            expect(result).toHaveProperty('redirect');
            expect((result as any).redirect.statusCode).toBe(302);
        });
    });

    describe('role authorization', () => {
        test('returns props when user has required role', async () => {
            const client = createMockClient(loggedInUserAuthResult, undefined);
            const result = await buildServerSideProps({
                layoutResult: createLayoutResult(),
                client,
                ssrExchange: createMockSsrExchange(),
                context: createMockContext(),
                domainConfig,
                authenticationConfig: {
                    authenticationRequired: true,
                    authorizedRoles: [TypeCustomerUserRoleEnum.RoleApiCartAndOrderCreation],
                },
            });

            expect(result).toHaveProperty('props');
            expect((result as any).props.isForbidden).toBe(false);
        });

        test('sets 403 status when user does not have required role', async () => {
            const client = createMockClient(loggedInUserAuthResult, undefined);
            const context = createMockContext();
            const result = await buildServerSideProps({
                layoutResult: createLayoutResult(),
                client,
                ssrExchange: createMockSsrExchange(),
                context,
                domainConfig,
                authenticationConfig: {
                    authenticationRequired: true,
                    authorizedRoles: [TypeCustomerUserRoleEnum.RoleApiCustomerSelfManage],
                },
            });

            expect(result).toHaveProperty('props');
            expect((result as any).props.isForbidden).toBe(true);
            expect(context.res.statusCode).toBe(403);
        });

        test('sets 403 when user is in wrong area', async () => {
            const client = createMockClient(loggedInUserAuthResult, undefined);
            const context = createMockContext();
            const result = await buildServerSideProps({
                layoutResult: createLayoutResult(),
                client,
                ssrExchange: createMockSsrExchange(),
                context,
                domainConfig: { ...domainConfig, type: CustomerUserAreaEnum.B2C },
                authenticationConfig: {
                    authenticationRequired: true,
                    authorizedAreas: [CustomerUserAreaEnum.B2B],
                },
            });

            expect(result).toHaveProperty('props');
            expect((result as any).props.isForbidden).toBe(true);
            expect(context.res.statusCode).toBe(403);
        });
    });

    describe('slug redirect', () => {
        test('returns 301 redirect when slug differs from resolved URL', async () => {
            const client = createMockClient(undefined, undefined);
            const result = await buildServerSideProps({
                layoutResult: createLayoutResult([{ data: { slug: { slug: '/canonical-url' } } }]),
                client,
                ssrExchange: createMockSsrExchange(),
                context: createMockContext('/old-url'),
                domainConfig,
            });

            expect(result).toHaveProperty('redirect');
            expect((result as any).redirect.statusCode).toBe(301);
            expect((result as any).redirect.destination).toBe('/canonical-url');
        });

        test('slug redirect takes priority over auth check', async () => {
            const client = createMockClient(loggedOutAuthResult, undefined);
            mockGetServerSideInternationalizedStaticUrl.mockReturnValue({
                trimmedUrlWithoutQueryParams: '/old-url',
                queryParams: null,
            });
            const result = await buildServerSideProps({
                layoutResult: createLayoutResult([{ data: { slug: { slug: '/canonical-url' } } }]),
                client,
                ssrExchange: createMockSsrExchange(),
                context: createMockContext('/old-url'),
                domainConfig,
                authenticationConfig: { authenticationRequired: true },
            });

            expect(result).toHaveProperty('redirect');
            expect((result as any).redirect.statusCode).toBe(301);
        });
    });

    describe('maintenance', () => {
        test('sets 503 status when any query returns 503', async () => {
            const client = createMockClient(loggedInUserAuthResult, undefined);
            const context = createMockContext();
            const result = await buildServerSideProps({
                layoutResult: createLayoutResult([{ data: {}, error: { response: { status: 503 } } }]),
                client,
                ssrExchange: createMockSsrExchange(),
                context,
                domainConfig,
            });

            expect(result).toHaveProperty('props');
            expect((result as any).props.isMaintenance).toBe(true);
            expect(context.res.statusCode).toBe(503);
        });

        test('does not set maintenance when no 503 error', async () => {
            const client = createMockClient(loggedInUserAuthResult, undefined);
            const context = createMockContext();
            const result = await buildServerSideProps({
                layoutResult: createLayoutResult([{ data: {} }]),
                client,
                ssrExchange: createMockSsrExchange(),
                context,
                domainConfig,
            });

            expect((result as any).props.isMaintenance).toBe(false);
        });
    });

    describe('CSP header', () => {
        test('sets CSP header from settings query result', async () => {
            const client = createMockClient(undefined, undefined);
            const context = createMockContext();
            await buildServerSideProps({
                layoutResult: createLayoutResult([{ data: { settings: { cspHeader: "default-src 'self'" } } }]),
                client,
                ssrExchange: createMockSsrExchange(),
                context,
                domainConfig,
            });

            expect(context.res.setHeader).toHaveBeenCalledWith('Content-Security-Policy', "default-src 'self'");
        });

        test('does not set CSP header when not present in results', async () => {
            const client = createMockClient(undefined, undefined);
            const context = createMockContext();
            await buildServerSideProps({
                layoutResult: createLayoutResult([{ data: {} }]),
                client,
                ssrExchange: createMockSsrExchange(),
                context,
                domainConfig,
            });

            expect(context.res.setHeader).not.toHaveBeenCalled();
        });
    });

    describe('props output', () => {
        test('includes customerUserRoles in props', async () => {
            const client = createMockClient(loggedInUserAuthResult, undefined);
            const result = await buildServerSideProps({
                layoutResult: createLayoutResult(),
                client,
                ssrExchange: createMockSsrExchange(),
                context: createMockContext(),
                domainConfig,
            });

            expect((result as any).props.customerUserRoles).toEqual([
                TypeCustomerUserRoleEnum.RoleApiCartAndOrderCreation,
            ]);
        });

        test('merges additionalProps into result', async () => {
            const client = createMockClient(undefined, undefined);
            const result = await buildServerSideProps({
                layoutResult: createLayoutResult(),
                client,
                ssrExchange: createMockSsrExchange(),
                context: createMockContext(),
                domainConfig,
                additionalProps: { customProp: 'value' },
            });

            expect((result as any).props.customProp).toBe('value');
        });

        test('includes urqlState from ssrExchange', async () => {
            const client = createMockClient(undefined, undefined);
            const ssrExchange = createMockSsrExchange();
            (ssrExchange.extractData as any).mockReturnValue({ someQuery: { data: 'test' } });

            const result = await buildServerSideProps({
                layoutResult: createLayoutResult(),
                client,
                ssrExchange,
                context: createMockContext(),
                domainConfig,
            });

            expect((result as any).props.urqlState).toEqual({ someQuery: { data: 'test' } });
        });
    });
});
