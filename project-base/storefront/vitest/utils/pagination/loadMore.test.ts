import { DEFAULT_PAGE_SIZE } from 'config/constants';
import { CategoryProductsQueryDocument } from 'graphql/requests/products/queries/CategoryProductsQuery.generated';
import { TypeProductOrderingModeEnum } from 'graphql/types';
import { stringify } from 'querystring';
import { UrlQueries } from 'types/urlQueries';
import { getOffsetPage } from 'utils/loadMore/getOffsetPage';
import { getOffsetPageAndLoadMore } from 'utils/loadMore/getOffsetPageAndLoadMore';
import { getPreviousProductsFromCache } from 'utils/loadMore/getPreviousProductsFromCache';
import { getRedirectWithOffsetPage } from 'utils/loadMore/getRedirectWithOffsetPage';
import {
    FILTER_QUERY_PARAMETER_NAME,
    LOAD_MORE_QUERY_PARAMETER_NAME,
    PAGE_QUERY_PARAMETER_NAME,
} from 'utils/queryParamNames';
import { describe, expect, test, vi } from 'vitest';

vi.mock('store/useSessionStore', () => ({}));

describe('getPreviouslyQueriedProductsFromCache tests', () => {
    const SLUG = '/url-slug';
    const client = {} as any;

    test('previous products should be undefined if loadMore is 0', () => {
        expect(
            getPreviousProductsFromCache(
                CategoryProductsQueryDocument,
                client,
                SLUG,
                TypeProductOrderingModeEnum.Priority,
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
                CategoryProductsQueryDocument,
                client,
                SLUG,
                TypeProductOrderingModeEnum.Priority,
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
                CategoryProductsQueryDocument,
                client,
                SLUG,
                TypeProductOrderingModeEnum.Priority,
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
                CategoryProductsQueryDocument,
                client,
                SLUG,
                TypeProductOrderingModeEnum.Priority,
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
        expect(getOffsetPageAndLoadMore(1, 0)).toBe(undefined);

        expect(getOffsetPageAndLoadMore(1, 1)).toBe(undefined);

        expect(getOffsetPageAndLoadMore(1, 2)).toBe(undefined);

        expect(getOffsetPageAndLoadMore(1, 1, 50)).toBe(undefined);

        expect(getOffsetPageAndLoadMore(2, 10, undefined, 310)).toBe(undefined);

        expect(getOffsetPageAndLoadMore(2, 10, undefined, 310)).toBe(undefined);

        expect(getOffsetPageAndLoadMore(1, 10, undefined, 310)).toBe(undefined);
    });

    test('loading too many products offsets the page', () => {
        expect(getOffsetPageAndLoadMore(1, 3)).toStrictEqual({
            updatedPage: 2,
            updatedLoadMore: 2,
        });

        expect(getOffsetPageAndLoadMore(1, 10)).toStrictEqual({
            updatedPage: 9,
            updatedLoadMore: 2,
        });

        expect(getOffsetPageAndLoadMore(2, 10, 50)).toStrictEqual({
            updatedPage: 11,
            updatedLoadMore: 1,
        });

        expect(getOffsetPageAndLoadMore(2, 10, 50, 310)).toStrictEqual({
            updatedPage: 7,
            updatedLoadMore: 5,
        });

        expect(getOffsetPageAndLoadMore(1, 11, undefined, 310)).toStrictEqual({
            updatedPage: 2,
            updatedLoadMore: 10,
        });

        expect(getOffsetPageAndLoadMore(2, 11, undefined, 310)).toStrictEqual({
            updatedPage: 3,
            updatedLoadMore: 10,
        });
    });
});

describe('getOffsetPage tests', () => {
    test('loading a valid number of products does not offset the page', () => {
        expect(getOffsetPage(1, 0)).toBe(undefined);
    });

    test('loading too many products offsets the page', () => {
        expect(getOffsetPage(1, 1)).toStrictEqual({
            updatedPage: 2,
            updatedLoadMore: 0,
        });

        expect(getOffsetPage(1, 2)).toStrictEqual({
            updatedPage: 3,
            updatedLoadMore: 0,
        });

        expect(getOffsetPage(2, 2)).toStrictEqual({
            updatedPage: 4,
            updatedLoadMore: 0,
        });

        expect(getOffsetPage(2, 10)).toStrictEqual({
            updatedPage: 12,
            updatedLoadMore: 0,
        });

        expect(getOffsetPage(1, 11)).toStrictEqual({
            updatedPage: 12,
            updatedLoadMore: 0,
        });

        expect(getOffsetPage(2, 11)).toStrictEqual({
            updatedPage: 13,
            updatedLoadMore: 0,
        });
    });
});

describe('getRedirectWithOffsetPage tests', () => {
    test('loading a valid number of products does not redirect the page', () => {
        expect(
            getRedirectWithOffsetPage(1, 0, '/my-url', {
                [PAGE_QUERY_PARAMETER_NAME]: '1',
                [LOAD_MORE_QUERY_PARAMETER_NAME]: '0',
            }),
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
                destination: '/my-url?page=13',
                permanent: false,
            },
        });

        expect(
            getRedirectWithOffsetPage(1, 11, '/my-url', {
                [LOAD_MORE_QUERY_PARAMETER_NAME]: '11',
            }),
        ).toStrictEqual({
            redirect: {
                destination: '/my-url?page=12',
                permanent: false,
            },
        });

        expect(
            getRedirectWithOffsetPage(2, 10, '/my-url', {
                [LOAD_MORE_QUERY_PARAMETER_NAME]: '11',
            }),
        ).toStrictEqual({
            redirect: {
                destination: '/my-url?page=12',
                permanent: false,
            },
        });

        expect(
            getRedirectWithOffsetPage(2, 10, '/my-url', {
                [LOAD_MORE_QUERY_PARAMETER_NAME]: '11',
            }),
        ).toStrictEqual({
            redirect: {
                destination: '/my-url?page=12',
                permanent: false,
            },
        });
    });

    test('redirecting correctly keeps other query parameters if both page and load more are kept', () => {
        const currentQueryWithoutDynamicPageQuery: UrlQueries = {
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

        currentQueryWithoutDynamicPageQuery[PAGE_QUERY_PARAMETER_NAME] = '13';
        delete currentQueryWithoutDynamicPageQuery[LOAD_MORE_QUERY_PARAMETER_NAME];

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

        expect(getRedirectWithOffsetPage(2, 11, '/my-url', currentQuery)).toStrictEqual({
            redirect: {
                destination: `/my-url?${stringify(currentQueryWithoutDynamicPageAndLoadMoreQuery)}`,
                permanent: false,
            },
        });
    });
});
