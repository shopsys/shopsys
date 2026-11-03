import { act, fireEvent, render, screen, waitFor } from '@testing-library/react';
import { cacheExchange } from '@urql/exchange-graphcache';
import { DEFAULT_PAGE_SIZE } from 'config/constants';
import { Kind } from 'graphql';
import { useState } from 'react';
import { hydrateRoot, Root } from 'react-dom/client';
import { renderToString } from 'react-dom/server';
import { Client, createRequest, Exchange, gql, Provider, ssrExchange, useQuery } from 'urql';
import { useProductsData } from 'utils/loadMore/useProductsData';
import { beforeEach, expect, test, vi } from 'vitest';
import { filter, map, pipe } from 'wonka';

const params = vi.hoisted(() => ({ sort: 'PRIORITY' }));

vi.mock('next/router', () => ({ useRouter: () => ({ asPath: '/category' }) }));
vi.mock('utils/queryParams/useCurrentFilterQuery', () => ({ useCurrentFilterQuery: () => null }));
vi.mock('utils/queryParams/useCurrentLoadMoreQuery', () => ({ useCurrentLoadMoreQuery: () => 0 }));
vi.mock('utils/queryParams/useCurrentPageQuery', () => ({ useCurrentPageQuery: () => 1 }));
vi.mock('utils/queryParams/useCurrentSortQuery', () => ({ useCurrentSortQuery: () => params.sort }));

const productsQuery = gql`
    query Products($urlSlug: String!, $orderingMode: String, $filter: ProductFilter, $endCursor: String, $pageSize: Int!) {
        products(urlSlug: $urlSlug, orderingMode: $orderingMode, filter: $filter, endCursor: $endCursor, pageSize: $pageSize) {
            edges { node { uuid fullName } }
            pageInfo { hasNextPage }
        }
    }
`;
const categoryQuery = gql`
    query Category {
        category { uuid bestsellers { uuid fullName } }
    }
`;
const product = (name: string) => ({ __typename: 'Product', uuid: 'product-1', fullName: name });
const products = (name: string) => ({
    products: {
        __typename: 'ProductConnection',
        edges: [{ __typename: 'ProductEdge', node: product(name) }],
        pageInfo: { __typename: 'PageInfo', hasNextPage: false },
    },
});
const createCache = () =>
    cacheExchange({
        keys: {
            Category: (data) => data.uuid as string,
            Product: (data) => data.uuid as string,
            ProductConnection: () => null,
            ProductEdge: () => null,
            PageInfo: () => null,
        },
    });

const Products = () => {
    const [, rerender] = useState(0);
    const { products: edges } = useProductsData(productsQuery, 1);
    return (
        <>
            <button
                onClick={() => {
                    params.sort = 'PRICE_ASC';
                    rerender((value) => value + 1);
                }}
            >
                Sort
            </button>
            {edges?.map(
                (edge) =>
                    edge?.node && (
                        <a href={`/product/${edge.node.uuid}`} key={edge.node.uuid}>
                            {edge.node.fullName}
                        </a>
                    ),
            )}
        </>
    );
};

beforeEach(() => {
    params.sort = 'PRIORITY';
});

test('updates a subscribed category after sorting without updating it during the product render', async () => {
    const error = vi.spyOn(console, 'error').mockImplementation(() => undefined);
    const requests: string[] = [];
    const network: Exchange = () => (operations) =>
        pipe(
            operations,
            filter((operation) => operation.kind === 'query'),
            map((operation) => {
                if (operation.variables?.orderingMode) {
                    requests.push(operation.variables.orderingMode);
                }
                return {
                    operation,
                    stale: false,
                    hasNext: false,
                    data: operation.query.definitions.some(
                        (definition) =>
                            definition.kind === Kind.OPERATION_DEFINITION && definition.name?.value === 'Category',
                    )
                        ? {
                              category: {
                                  __typename: 'Category',
                                  uuid: 'category-1',
                                  bestsellers: [product('Original')],
                              },
                          }
                        : products(operation.variables?.orderingMode === 'PRICE_ASC' ? 'Updated' : 'Original'),
                };
            }),
        );
    const client = new Client({ url: 'https://example.com/graphql', exchanges: [createCache(), network] });
    const Category = () => {
        const [{ data }] = useQuery({ query: categoryQuery });
        return (
            <>
                <p>{data?.category.bestsellers[0].fullName}</p>
                <Products />
            </>
        );
    };

    render(
        <Provider value={client}>
            <Category />
        </Provider>,
    );
    await screen.findByRole('link', { name: 'Original' });
    fireEvent.click(screen.getByRole('button', { name: 'Sort' }));
    await screen.findByRole('link', { name: 'Updated' });
    await waitFor(() => expect(screen.getByText('Updated', { selector: 'p' })).toBeInTheDocument());
    expect(error.mock.calls.filter(([message]) => String(message).includes('Cannot update a component'))).toEqual([]);
    expect(requests).toEqual(['PRIORITY', 'PRICE_ASC']);
});

test('keeps product links in the first render and hydrates the SSR result without mismatches', async () => {
    const request = createRequest(productsQuery, {
        urlSlug: 'category',
        orderingMode: 'PRIORITY',
        filter: null,
        endCursor: '',
        pageSize: DEFAULT_PAGE_SIZE,
    });
    const initialState = { [request.key]: { data: JSON.stringify(products('Hydrated product')) } };
    const network: Exchange = () => (operations) =>
        pipe(
            operations,
            filter((operation) => operation.kind === 'query'),
            map((operation) => ({ operation, data: undefined, stale: false, hasNext: false })),
        );
    const server = new Client({
        url: 'https://example.com/graphql',
        exchanges: [ssrExchange({ isClient: false, initialState }), network],
    });
    const createBrowserClient = () =>
        new Client({
            url: 'https://example.com/graphql',
            exchanges: [createCache(), ssrExchange({ isClient: true, initialState }), network],
        });

    const serverHtml = renderToString(
        <Provider value={server}>
            <Products />
        </Provider>,
    );
    const browserHtml = renderToString(
        <Provider value={createBrowserClient()}>
            <Products />
        </Provider>,
    );
    expect(serverHtml).toContain('Hydrated product');
    expect(serverHtml).toContain('href="/product/product-1"');
    expect(browserHtml).toBe(serverHtml);

    const container = document.createElement('div');
    container.innerHTML = serverHtml;
    document.body.append(container);
    const recoverableError = vi.fn();
    let root: Root | undefined;
    try {
        await act(async () => {
            root = hydrateRoot(
                container,
                <Provider value={createBrowserClient()}>
                    <Products />
                </Provider>,
                {
                    onRecoverableError: recoverableError,
                },
            );
        });
        expect(container.querySelector('a')).toHaveTextContent('Hydrated product');
        expect(recoverableError).not.toHaveBeenCalled();
    } finally {
        await act(async () => root?.unmount());
        container.remove();
    }
});
