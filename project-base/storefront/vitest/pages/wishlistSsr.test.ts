import { WishlistPageQueryDocument } from 'graphql/requests/productLists/queries/WishlistPageQuery.generated';
import { TypeProductListTypeEnum } from 'graphql/types';
import { getServerSideProps } from 'pages/wishlist';
// biome-ignore lint/style/noRestrictedImports: Exercise SSR queries through a controlled GraphQL transport.
import { createRequest, createClient as createUrqlClient, fetchExchange, ssrExchange } from 'urql';
import { createClient } from 'urql/createClient';
import { beforeEach, describe, expect, test, vi } from 'vitest';
import { defaultTestDomainConfig } from 'vitest/helpers/mockPublicConfig';

vi.mock('utils/serverSide/getServerSidePropsWrapper', () => ({
    getServerSidePropsWrapper: (callback: unknown) => callback,
}));
vi.mock('urql/createClient', () => ({ createClient: vi.fn() }));
vi.mock('config/staticRewritePaths', () => ({
    STATIC_REWRITE_PATHS: { 'https://test1.example.com/': { '/wishlist': '/wishlist' } },
}));
vi.mock('next-translate/loadNamespaces', () => ({
    default: () => Promise.resolve({ __lang: 'en', __namespaces: {} }),
}));

describe('wishlist SSR', () => {
    let currentCustomerUser: { uuid: string; roles: string[] } | null | undefined;
    let requestedOperations: string[];

    const runSsr = async (isFullPageRequest = false) => {
        const ssrCache = ssrExchange({ isClient: false });
        const client = createUrqlClient({
            url: 'https://api.example.com/graphql',
            exchanges: [ssrCache, fetchExchange],
            preferGetMethod: false,
            fetch: async (_input, init) => {
                const { operationName, variables } = JSON.parse(init?.body as string);
                requestedOperations.push(operationName);

                if (operationName === 'CurrentCustomerUserAuthQuery') {
                    if (currentCustomerUser === undefined) {
                        return Response.json({ errors: [{ message: 'Authentication failed' }] });
                    }
                    return Response.json({ data: { currentCustomerUser } });
                }

                if (operationName === 'ProductListSummaryQuery') {
                    expect(variables).toEqual({ input: { type: 'WISHLIST', uuid: null } });
                    return Response.json({
                        data: {
                            productList: {
                                __typename: 'ProductList',
                                uuid: 'customer-wishlist',
                                itemsCount: 0,
                                productUuids: [],
                            },
                        },
                    });
                }

                if (operationName === 'WishlistPageQuery') {
                    expect(variables).toEqual({ input: { type: 'WISHLIST', uuid: null }, offset: 0, limit: 30 });
                    if (!currentCustomerUser) {
                        return Response.json({ errors: [{ message: 'Invalid product list criteria' }] });
                    }

                    return Response.json({ data: { productList: { uuid: 'customer-wishlist', products: [] } } });
                }

                return Response.json({ data: {} });
            },
        });
        vi.mocked(createClient).mockReturnValue(client);

        return getServerSideProps({
            redisClient: undefined,
            domainConfig: defaultTestDomainConfig,
            ssrExchange: ssrCache,
            t: (key: string) => key,
        })({
            req: { headers: isFullPageRequest ? {} : { 'x-nextjs-data': '1' } },
            res: { statusCode: 200, setHeader: vi.fn() },
            query: {},
            resolvedUrl: '/wishlist',
        });
    };

    beforeEach(() => {
        currentCustomerUser = null;
        requestedOperations = [];
    });

    describe.each([true, false])('full page request: %s', (isFullPageRequest) => {
        test('does not request a wishlist without a UUID for an anonymous visitor', async () => {
            const result = await runSsr(isFullPageRequest);

            expect(result).toHaveProperty('props.isMaintenance', false);
            expect(requestedOperations).toContain('CurrentCustomerUserAuthQuery');
            expect(requestedOperations).not.toContain('WishlistPageQuery');
        });

        test('includes the authenticated customer wishlist in the hydration data', async () => {
            currentCustomerUser = { uuid: 'customer', roles: [] };
            const result = await runSsr(isFullPageRequest);
            const wishlistKey = createRequest(WishlistPageQueryDocument, {
                input: { type: TypeProductListTypeEnum.Wishlist, uuid: null },
                offset: 0,
                limit: 30,
            }).key;

            expect(requestedOperations).toContain('WishlistPageQuery');
            expect(JSON.parse(result.props.urqlState[wishlistKey].data)).toEqual({
                productList: { uuid: 'customer-wishlist', products: [] },
            });
        });
    });

    test('does not request a wishlist when authentication fails', async () => {
        currentCustomerUser = undefined;

        await runSsr();

        expect(requestedOperations).toEqual(['CurrentCustomerUserAuthQuery']);
    });
});
