import { ProductOrderingModeEnumApi } from 'graphql/requests/types';
import {
    FILTER_QUERY_PARAMETER_NAME,
    LOAD_MORE_QUERY_PARAMETER_NAME,
    PAGE_QUERY_PARAMETER_NAME,
} from 'helpers/queryParamNames';
import { useQueryParams } from 'hooks/useQueryParams';
import { useRouter } from 'next/router';
import { describe, expect, Mock, test, vi } from 'vitest';

const CATEGORY_URL = '/category-url';
const CATEGORY_PATHNAME = '/categories/[categorySlug]';

const mockPush = vi.fn();
vi.mock('next/router', () => ({
    useRouter: vi.fn(() => ({
        pathname: CATEGORY_PATHNAME,
        asPath: CATEGORY_URL,
        push: mockPush,
        query: {},
    })),
}));

vi.mock('store/useSessionStore', () => ({
    useSessionStore: vi.fn((selector) => {
        return selector({
            defaultProductFiltersMap: {
                flags: new Set(),
                sort: ProductOrderingModeEnumApi.PriorityApi,
                parameters: new Map(),
            },
            originalCategorySlug: null,
        });
    }),
}));

describe('useQueryParams().updateFilterFlags tests', () => {
    test('flag should be added to query if not present', () => {
        useQueryParams().updateFilterFlags('test-flag');

        expect(mockPush).toBeCalledWith(
            {
                pathname: CATEGORY_PATHNAME,
                query: {
                    categorySlug: CATEGORY_URL,
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                        flags: ['test-flag'],
                    }),
                },
            },
            {
                pathname: CATEGORY_URL,
                query: {
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                        flags: ['test-flag'],
                    }),
                },
            },
            {
                shallow: true,
            },
        );
    });

    test('flag should be removed from query if already present', () => {
        (useRouter as Mock).mockImplementation(() => ({
            pathname: CATEGORY_PATHNAME,
            asPath: CATEGORY_URL,
            push: mockPush,
            query: { [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({ flags: ['test-flag'] }) },
        }));

        useQueryParams().updateFilterFlags('test-flag');

        expect(mockPush).toBeCalledWith(
            {
                pathname: CATEGORY_PATHNAME,
                query: { categorySlug: CATEGORY_URL },
            },
            {
                pathname: CATEGORY_URL,
                query: {},
            },
            {
                shallow: true,
            },
        );
    });

    test('changing flags resets page and load more', () => {
        (useRouter as Mock).mockImplementation(() => ({
            pathname: CATEGORY_PATHNAME,
            asPath: CATEGORY_URL,
            push: mockPush,
            query: {
                [PAGE_QUERY_PARAMETER_NAME]: '2',
                [LOAD_MORE_QUERY_PARAMETER_NAME]: '2',
            },
        }));

        useQueryParams().updateFilterFlags('test-flag');

        expect(mockPush).toBeCalledWith(
            {
                pathname: CATEGORY_PATHNAME,
                query: {
                    categorySlug: CATEGORY_URL,
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                        flags: ['test-flag'],
                    }),
                },
            },
            {
                pathname: CATEGORY_URL,
                query: {
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                        flags: ['test-flag'],
                    }),
                },
            },
            {
                shallow: true,
            },
        );
    });
});
