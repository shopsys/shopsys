import { ProductOrderingModeEnumApi } from 'graphql/requests/types';
import {
    FILTER_QUERY_PARAMETER_NAME,
    LOAD_MORE_QUERY_PARAMETER_NAME,
    PAGE_QUERY_PARAMETER_NAME,
} from 'helpers/queryParamNames';
import { useQueryParams } from 'hooks/useQueryParams';
import { describe, expect, Mock, test, vi } from 'vitest';

const CATEGORY_URL = '/category-url';
const CATEGORY_PATHNAME = '/categories/[categorySlug]';
const mockDefaultSort = vi.fn(() => ProductOrderingModeEnumApi.PriorityApi);

const mockPush = vi.fn();
const mockPageQueryGetter = vi.fn();
const mockLoadMoreQueryGetter = vi.fn();
vi.mock('next/router', () => ({
    useRouter: vi.fn(() => ({
        pathname: CATEGORY_PATHNAME,
        asPath: CATEGORY_URL,
        push: mockPush,
        query: {
            [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                brands: ['test-brand'],
                flags: ['test-flag'],
                parameters: [
                    {
                        parameter: 'default-parameter-1',
                        values: ['default-parameter-value-1'],
                    },
                    {
                        parameter: 'default-parameter-2',
                        values: ['default-parameter-value-3', 'default-parameter-value-4'],
                    },
                ],
            }),
            get [PAGE_QUERY_PARAMETER_NAME]() {
                return mockPageQueryGetter();
            },
            get [LOAD_MORE_QUERY_PARAMETER_NAME]() {
                return mockLoadMoreQueryGetter();
            },
        },
    })),
}));

vi.mock('store/useSessionStore', () => ({
    useSessionStore: vi.fn((selector) => {
        return selector({
            defaultProductFiltersMap: {
                flags: new Set(),
                sort: mockDefaultSort(),
                parameters: new Map(),
            },
            originalCategorySlug: null,
        });
    }),
}));

describe('useQueryParams().loadMore tests', () => {
    test('loading 1 "load more" page while on the 1st page should only update the load more URL query', () => {
        useQueryParams().loadMore();

        expect(mockPush).toBeCalledWith(
            {
                pathname: CATEGORY_PATHNAME,
                query: {
                    categorySlug: CATEGORY_URL,
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                        brands: ['test-brand'],
                        flags: ['test-flag'],
                        parameters: [
                            {
                                parameter: 'default-parameter-1',
                                values: ['default-parameter-value-1'],
                            },
                            {
                                parameter: 'default-parameter-2',
                                values: ['default-parameter-value-3', 'default-parameter-value-4'],
                            },
                        ],
                    }),
                    [LOAD_MORE_QUERY_PARAMETER_NAME]: '1',
                },
            },
            {
                pathname: CATEGORY_URL,
                query: {
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                        brands: ['test-brand'],
                        flags: ['test-flag'],
                        parameters: [
                            {
                                parameter: 'default-parameter-1',
                                values: ['default-parameter-value-1'],
                            },
                            {
                                parameter: 'default-parameter-2',
                                values: ['default-parameter-value-3', 'default-parameter-value-4'],
                            },
                        ],
                    }),
                    [LOAD_MORE_QUERY_PARAMETER_NAME]: '1',
                },
            },
            {
                shallow: true,
            },
        );
    });

    test('loading 1 "load more" page while on a page other than the 1st one should only update the load more URL query', () => {
        (mockPageQueryGetter as Mock).mockImplementation(() => '2');

        useQueryParams().loadMore();

        expect(mockPush).toBeCalledWith(
            {
                pathname: CATEGORY_PATHNAME,
                query: {
                    categorySlug: CATEGORY_URL,
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                        brands: ['test-brand'],
                        flags: ['test-flag'],
                        parameters: [
                            {
                                parameter: 'default-parameter-1',
                                values: ['default-parameter-value-1'],
                            },
                            {
                                parameter: 'default-parameter-2',
                                values: ['default-parameter-value-3', 'default-parameter-value-4'],
                            },
                        ],
                    }),
                    [LOAD_MORE_QUERY_PARAMETER_NAME]: '1',
                    [PAGE_QUERY_PARAMETER_NAME]: '2',
                },
            },
            {
                pathname: CATEGORY_URL,
                query: {
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                        brands: ['test-brand'],
                        flags: ['test-flag'],
                        parameters: [
                            {
                                parameter: 'default-parameter-1',
                                values: ['default-parameter-value-1'],
                            },
                            {
                                parameter: 'default-parameter-2',
                                values: ['default-parameter-value-3', 'default-parameter-value-4'],
                            },
                        ],
                    }),
                    [LOAD_MORE_QUERY_PARAMETER_NAME]: '1',
                    [PAGE_QUERY_PARAMETER_NAME]: '2',
                },
            },
            {
                shallow: true,
            },
        );
    });

    test('loading 2 "load more" pages while on the 1st page should only update the load more URL query', () => {
        (mockLoadMoreQueryGetter as Mock).mockImplementation(() => '1');

        useQueryParams().loadMore();

        expect(mockPush).toBeCalledWith(
            {
                pathname: CATEGORY_PATHNAME,
                query: {
                    categorySlug: CATEGORY_URL,
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                        brands: ['test-brand'],
                        flags: ['test-flag'],
                        parameters: [
                            {
                                parameter: 'default-parameter-1',
                                values: ['default-parameter-value-1'],
                            },
                            {
                                parameter: 'default-parameter-2',
                                values: ['default-parameter-value-3', 'default-parameter-value-4'],
                            },
                        ],
                    }),
                    [LOAD_MORE_QUERY_PARAMETER_NAME]: '2',
                },
            },
            {
                pathname: CATEGORY_URL,
                query: {
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                        brands: ['test-brand'],
                        flags: ['test-flag'],
                        parameters: [
                            {
                                parameter: 'default-parameter-1',
                                values: ['default-parameter-value-1'],
                            },
                            {
                                parameter: 'default-parameter-2',
                                values: ['default-parameter-value-3', 'default-parameter-value-4'],
                            },
                        ],
                    }),
                    [LOAD_MORE_QUERY_PARAMETER_NAME]: '2',
                },
            },
            {
                shallow: true,
            },
        );
    });

    test('loading 2 "load more" pages while on a page other than the 1st one should only update the load more URL query', () => {
        (mockPageQueryGetter as Mock).mockImplementation(() => '2');
        (mockLoadMoreQueryGetter as Mock).mockImplementation(() => '1');

        useQueryParams().loadMore();

        expect(mockPush).toBeCalledWith(
            {
                pathname: CATEGORY_PATHNAME,
                query: {
                    categorySlug: CATEGORY_URL,
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                        brands: ['test-brand'],
                        flags: ['test-flag'],
                        parameters: [
                            {
                                parameter: 'default-parameter-1',
                                values: ['default-parameter-value-1'],
                            },
                            {
                                parameter: 'default-parameter-2',
                                values: ['default-parameter-value-3', 'default-parameter-value-4'],
                            },
                        ],
                    }),
                    [LOAD_MORE_QUERY_PARAMETER_NAME]: '2',
                    [PAGE_QUERY_PARAMETER_NAME]: '2',
                },
            },
            {
                pathname: CATEGORY_URL,
                query: {
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                        brands: ['test-brand'],
                        flags: ['test-flag'],
                        parameters: [
                            {
                                parameter: 'default-parameter-1',
                                values: ['default-parameter-value-1'],
                            },
                            {
                                parameter: 'default-parameter-2',
                                values: ['default-parameter-value-3', 'default-parameter-value-4'],
                            },
                        ],
                    }),
                    [LOAD_MORE_QUERY_PARAMETER_NAME]: '2',
                    [PAGE_QUERY_PARAMETER_NAME]: '2',
                },
            },
            {
                shallow: true,
            },
        );
    });
});
