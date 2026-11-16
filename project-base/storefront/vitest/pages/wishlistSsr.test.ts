import { getEndCursor } from 'components/Blocks/Product/Filter/utils/getEndCursor';
import { DEFAULT_PAGE_SIZE } from 'config/constants';
import { getOperationAST, Kind, parse } from 'graphql';
import {
    TypeWishlistPageQueryVariables,
    WishlistPageQueryDocument,
} from 'graphql/requests/productLists/queries/WishlistPageQuery.generated';
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
    let requestedPages: TypeWishlistPageQueryVariables[];
    let variableErrors: string[];
    let count: number;
    let summaryError: boolean;

    const runSsr = async (isFullPageRequest = false, query: Record<string, string> = {}) => {
        const ssrCache = ssrExchange({ isClient: false });
        const client = createUrqlClient({
            url: 'https://api.example.com/graphql',
            exchanges: [ssrCache, fetchExchange],
            preferGetMethod: false,
            fetch: async (_input, init) => {
                const { operationName, variables, query: document } = JSON.parse(init?.body as string);
                requestedOperations.push(operationName);

                if (operationName === 'CurrentCustomerUserAuthQuery') {
                    if (currentCustomerUser === undefined) {
                        return Response.json({ errors: [{ message: 'Authentication failed' }] });
                    }
                    return Response.json({ data: { currentCustomerUser } });
                }

                if (operationName === 'ProductListSummaryQuery') {
                    if (summaryError) return Response.json({ errors: [{ message: 'List unavailable' }] });
                    expect(variables).toEqual({ input: { type: 'WISHLIST', uuid: null } });
                    return Response.json({
                        data: {
                            productList: {
                                __typename: 'ProductList',
                                uuid: 'customer-wishlist',
                                itemsCount: count,
                                productUuids: Array.from({ length: count }, (_, index) => `product-${index + 1}`),
                            },
                        },
                    });
                }

                if (operationName === 'WishlistPageQuery') {
                    requestedPages.push(variables);
                    // Read required variables from the actual operation sent by SSR, not a duplicated query fixture.
                    const operation = getOperationAST(parse(document));
                    const missing =
                        operation?.variableDefinitions?.filter(
                            (definition) =>
                                definition.type.kind === Kind.NON_NULL_TYPE &&
                                variables[definition.variable.name.value] == null,
                        ) ?? [];
                    variableErrors.push(
                        ...missing.map((definition) => `Missing required variable $${definition.variable.name.value}`),
                    );
                    if (missing.length) return Response.json({ errors: [{ message: variableErrors.join(', ') }] });
                    if (!currentCustomerUser)
                        return Response.json({ errors: [{ message: 'Invalid product list criteria' }] });

                    return Response.json({
                        data: {
                            productList: {
                                __typename: 'ProductList',
                                uuid: 'customer-wishlist',
                                productsConnection: { __typename: 'ProductListProductConnection', edges: [] },
                            },
                        },
                    });
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
            query,
            resolvedUrl: '/wishlist',
        });
    };

    beforeEach(() => {
        currentCustomerUser = null;
        requestedOperations = [];
        requestedPages = [];
        variableErrors = [];
        count = 120;
        summaryError = false;
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
                input: { type: TypeProductListTypeEnum.Wishlist, uuid: 'customer-wishlist' },
                endCursor: getEndCursor(1),
                pageSize: DEFAULT_PAGE_SIZE,
            }).key;

            expect(variableErrors).toEqual([]);
            expect(requestedOperations).toContain('WishlistPageQuery');
            expect(JSON.parse(result.props.urqlState[wishlistKey].data)).toEqual({
                productList: {
                    __typename: 'ProductList',
                    uuid: 'customer-wishlist',
                    productsConnection: { __typename: 'ProductListProductConnection', edges: [] },
                },
            });
        });
    });

    test.each([
        [{ page: '2' }, 120, [2]],
        [{ page: '999' }, 61, [3]],
        [{ page: '-1', lm: '-2' }, 61, [1]],
    ])('prefetches bounded client-compatible pages for %j', async (query, total, pages) => {
        currentCustomerUser = { uuid: 'customer', roles: [] };
        count = total;
        const result = await runSsr(false, query);
        const expected = pages.map(
            (page): TypeWishlistPageQueryVariables => ({
                input: { type: TypeProductListTypeEnum.Wishlist, uuid: 'customer-wishlist' },
                endCursor: getEndCursor(page),
                pageSize: DEFAULT_PAGE_SIZE,
            }),
        );

        expect(variableErrors).toEqual([]);
        expect(requestedPages).toEqual(expected);
        for (const variables of expected) {
            const key = createRequest(WishlistPageQueryDocument, variables).key;
            expect(result.props.urqlState[key].error).toBeUndefined();
            expect(JSON.parse(result.props.urqlState[key].data).productList.productsConnection.edges).toEqual([]);
        }
    });

    test.each([true, false])('redirects load-more URLs before fetching data (full page: %s)', async (fullPage) => {
        currentCustomerUser = { uuid: 'customer', roles: [] };
        count = 9000;

        const result = await runSsr(fullPage, { page: '2', lm: '999', campaign: 'saved' });

        const destination = new URL(result.redirect.destination, 'https://test1.example.com');
        expect(destination.pathname).toBe('/wishlist');
        expect(Object.fromEntries(destination.searchParams)).toEqual({ campaign: 'saved', page: '1001' });
        expect(result.redirect.permanent).toBe(false);
        expect(requestedOperations).toEqual([]);
    });

    test.each(['empty', 'error'])('skips product cards when summary is %s', async (state) => {
        currentCustomerUser = { uuid: 'customer', roles: [] };
        count = 0;
        summaryError = state === 'error';

        await runSsr();

        expect(requestedOperations).toContain('ProductListSummaryQuery');
        expect(requestedPages).toEqual([]);
    });

    test('does not request a wishlist when authentication fails', async () => {
        currentCustomerUser = undefined;

        await runSsr();

        expect(requestedOperations).toEqual(['CurrentCustomerUserAuthQuery']);
    });
});
