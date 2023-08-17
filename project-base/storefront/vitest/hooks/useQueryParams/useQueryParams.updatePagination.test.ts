import { ProductOrderingModeEnumApi } from 'graphql/requests/types';
import { PAGE_QUERY_PARAMETER_NAME } from 'helpers/queryParamNames';
import { useQueryParams } from 'hooks/useQueryParams';
import { describe, expect, test, vi } from 'vitest';

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

describe('useQueryParams().updatePagination tests', () => {
    test('page should not be updated if page number is 1', () => {
        useQueryParams().updatePagination(1);

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

    test('page should not be updated if page number is greater than 1', () => {
        useQueryParams().updatePagination(2);

        expect(mockPush).toBeCalledWith(
            {
                pathname: CATEGORY_PATHNAME,
                query: { categorySlug: CATEGORY_URL, [PAGE_QUERY_PARAMETER_NAME]: '2' },
            },
            {
                pathname: CATEGORY_URL,
                query: {
                    [PAGE_QUERY_PARAMETER_NAME]: '2',
                },
            },
            {
                shallow: true,
            },
        );
    });
});
