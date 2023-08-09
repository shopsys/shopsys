import { DEFAULT_PAGE_SIZE } from 'config/constants';
import { CategoryProductsQueryDocumentApi, ProductOrderingModeEnumApi } from 'graphql/generated';
import {
    getOffsetPageAndLoadMore,
    getPreviousProductsFromCache,
    getRedirectWithOffsetPage,
} from 'helpers/pagination/loadMore';
import {
    FILTER_QUERY_PARAMETER_NAME,
    LOAD_MORE_QUERY_PARAMETER_NAME,
    PAGE_QUERY_PARAMETER_NAME,
} from 'helpers/queryParams/queryParamNames';
import { stringify } from 'querystring';
import { describe, expect, test, vi } from 'vitest';

vi.mock('store/useSessionStore', () => ({}));

describe('getPreviouslyQueriedProductsFromCache tests', () => {
    const SLUG = '/url-slug';
    const client = {} as any;

    test('previous products should be undefined if loadMore is 0', () => {
        expect(
            getPreviousProductsFromCache(
                CategoryProductsQueryDocumentApi,
                client,
                SLUG,
                ProductOrderingModeEnumApi.PriorityApi,
                null,
                DEFAULT_PAGE_SIZE,
                DEFAULT_PAGE_SIZE,
                1,
                0,
                (() => ({ products: ['mock product'] })) as unknown as () => any,
            ),
        ).toBe(undefined);
    });

    test('previous products should be undefined if any of the slices are undefined', () => {
        let callCounter = 0;
        expect(
            getPreviousProductsFromCache(
                CategoryProductsQueryDocumentApi,
                client,
                SLUG,
                ProductOrderingModeEnumApi.PriorityApi,
                null,
                DEFAULT_PAGE_SIZE,
                DEFAULT_PAGE_SIZE,
                1,
                3,
                (() => {
                    callCounter++;

                    if (callCounter === 2) {
                        return {
                            products: undefined,
                        };
                    }

                    return {
                        products: ['mock product', 'mock product 2'],
                    };
                }) as unknown as () => any,
            ),
        ).toBe(undefined);
    });

    test('previous products should return one slice if loadMore is 1', () => {
        expect(
            getPreviousProductsFromCache(
                CategoryProductsQueryDocumentApi,
                client,
                SLUG,
                ProductOrderingModeEnumApi.PriorityApi,
                null,
                DEFAULT_PAGE_SIZE,
                DEFAULT_PAGE_SIZE,
                1,
                1,
                (() => ({
                    products: ['mock product', 'mock product 2'],
                })) as unknown as () => any,
            ),
        ).toStrictEqual(['mock product', 'mock product 2']);
    });

    test('previous products should return one slice if loadMore is 1', () => {
        expect(
            getPreviousProductsFromCache(
                CategoryProductsQueryDocumentApi,
                client,
                SLUG,
                ProductOrderingModeEnumApi.PriorityApi,
                null,
                DEFAULT_PAGE_SIZE,
                DEFAULT_PAGE_SIZE,
                1,
                2,
                (() => ({
                    products: ['mock product', 'mock product 2'],
                })) as unknown as () => any,
            ),
        ).toStrictEqual(['mock product', 'mock product 2', 'mock product', 'mock product 2']);
    });
});

describe('getOffsetPageAndLoadMore tests', () => {
    test('loading a valid number of products does not offset the page', () => {
        expect(getOffsetPageAndLoadMore(2, 10)).toBe(undefined);

        expect(getOffsetPageAndLoadMore(1, 10)).toBe(undefined);

        expect(getOffsetPageAndLoadMore(1, 0)).toBe(undefined);

        expect(getOffsetPageAndLoadMore(1, 1, 50)).toBe(undefined);
    });

    test('loading too many products offsets the page', () => {
        expect(getOffsetPageAndLoadMore(2, 11)).toStrictEqual({
            updatedPage: 3,
            updatedLoadMore: 10,
        });

        expect(getOffsetPageAndLoadMore(1, 11)).toStrictEqual({
            updatedPage: 2,
            updatedLoadMore: 10,
        });

        expect(getOffsetPageAndLoadMore(2, 10, 50)).toStrictEqual({
            updatedPage: 11,
            updatedLoadMore: 1,
        });
    });
});

describe('getRedirectWithOffsetPage tests', () => {
    test('loading a valid number of products does not redirect the page', () => {
        expect(
            getRedirectWithOffsetPage(2, 10, '/my-url', {
                [PAGE_QUERY_PARAMETER_NAME]: '2',
                [LOAD_MORE_QUERY_PARAMETER_NAME]: '10',
            }),
        ).toBe(undefined);

        expect(
            getRedirectWithOffsetPage(1, 10, '/my-url', {
                [LOAD_MORE_QUERY_PARAMETER_NAME]: '10',
            }),
        ).toBe(undefined);

        expect(getRedirectWithOffsetPage(1, 0, '/my-url', {})).toBe(undefined);

        expect(
            getRedirectWithOffsetPage(
                1,
                1,
                '/my-url',
                {
                    [LOAD_MORE_QUERY_PARAMETER_NAME]: '1',
                },
                50,
            ),
        ).toBe(undefined);
    });

    test('loading too many products redirects the page', () => {
        expect(
            getRedirectWithOffsetPage(2, 11, '/my-url', {
                [PAGE_QUERY_PARAMETER_NAME]: '2',
                [LOAD_MORE_QUERY_PARAMETER_NAME]: '11',
            }),
        ).toStrictEqual({
            redirect: {
                destination: '/my-url?page=3&lm=10',
                permanent: false,
            },
        });

        expect(
            getRedirectWithOffsetPage(1, 11, '/my-url', {
                [LOAD_MORE_QUERY_PARAMETER_NAME]: '11',
            }),
        ).toStrictEqual({
            redirect: {
                destination: '/my-url?lm=10&page=2',
                permanent: false,
            },
        });

        expect(
            getRedirectWithOffsetPage(
                2,
                10,
                '/my-url',
                {
                    [LOAD_MORE_QUERY_PARAMETER_NAME]: '11',
                },
                50,
            ),
        ).toStrictEqual({
            redirect: {
                destination: '/my-url?lm=1&page=11',
                permanent: false,
            },
        });

        expect(
            getRedirectWithOffsetPage(
                2,
                10,
                '/my-url',
                {
                    [LOAD_MORE_QUERY_PARAMETER_NAME]: '11',
                },
                100,
            ),
        ).toStrictEqual({
            redirect: {
                destination: '/my-url?page=12',
                permanent: false,
            },
        });
    });

    test('redirecting correctly keeps other query parameters if both page and load more are kept', () => {
        const currentQueryWithoutDynamicPageQuery = {
            [PAGE_QUERY_PARAMETER_NAME]: '2',
            [LOAD_MORE_QUERY_PARAMETER_NAME]: '11',
            [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                flags: ['default-flag-1', 'default-flag-2'],
                parameters: [
                    {
                        parameter: 'default-parameter-1',
                        values: ['default-parameter-value-1', 'default-parameter-value-2'],
                    },
                    {
                        parameter: 'default-parameter-2',
                        values: ['default-parameter-value-3'],
                    },
                    {
                        parameter: 'default-parameter-3',
                        minimalValue: 100,
                        maximalValue: 1000,
                    },
                ],
            }),
        };

        const currentQuery = {
            ...currentQueryWithoutDynamicPageQuery,
            categorySlug: '/category-url',
        };

        currentQueryWithoutDynamicPageQuery[PAGE_QUERY_PARAMETER_NAME] = '3';
        currentQueryWithoutDynamicPageQuery[LOAD_MORE_QUERY_PARAMETER_NAME] = '10';

        expect(getRedirectWithOffsetPage(2, 11, '/my-url', currentQuery)).toStrictEqual({
            redirect: {
                destination: `/my-url?${stringify(currentQueryWithoutDynamicPageQuery)}`,
                permanent: false,
            },
        });
    });

    test('redirecting correctly keeps other query parameters if only page is kept', () => {
        const currentQueryWithoutDynamicPageAndLoadMoreQuery = {
            [PAGE_QUERY_PARAMETER_NAME]: '2',
            [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                flags: ['default-flag-1', 'default-flag-2'],
                parameters: [
                    {
                        parameter: 'default-parameter-1',
                        values: ['default-parameter-value-1', 'default-parameter-value-2'],
                    },
                    {
                        parameter: 'default-parameter-2',
                        values: ['default-parameter-value-3'],
                    },
                    {
                        parameter: 'default-parameter-3',
                        minimalValue: 100,
                        maximalValue: 1000,
                    },
                ],
            }),
        };

        const currentQuery = {
            ...currentQueryWithoutDynamicPageAndLoadMoreQuery,
            categorySlug: '/category-url',
        };

        currentQueryWithoutDynamicPageAndLoadMoreQuery[PAGE_QUERY_PARAMETER_NAME] = '13';

        expect(getRedirectWithOffsetPage(2, 11, '/my-url', currentQuery, 100)).toStrictEqual({
            redirect: {
                destination: `/my-url?${stringify(currentQueryWithoutDynamicPageAndLoadMoreQuery)}`,
                permanent: false,
            },
        });
    });
});
