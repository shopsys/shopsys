import { ProductOrderingModeEnumApi } from 'graphql/generated';
import { FILTER_QUERY_PARAMETER_NAME } from 'helpers/queryParams/queryParamNames';
import { useQueryParams } from 'hooks/useQueryParams';
import { useRouter } from 'next/router';
import { describe, expect, Mock, test, vi } from 'vitest';

const CATEGORY_URL = '/category-url';

const mockPush = vi.fn();
vi.mock('next/router', () => ({
    useRouter: vi.fn(() => ({
        asPath: CATEGORY_URL,
        push: mockPush,
        query: {},
    })),
}));

vi.mock('store/zustand/useSessionStore', () => ({
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
        (useRouter as Mock).mockImplementation(() => ({
            asPath: CATEGORY_URL,
            push: mockPush,
            query: {},
        }));

        useQueryParams().updateFilterFlags('test-flag');

        expect(mockPush).toBeCalledWith(
            {
                pathname: CATEGORY_URL,
                query: {
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                        flags: ['test-flag'],
                    }),
                },
            },
            undefined,
            {
                shallow: true,
            },
        );
    });

    test('flag should be removed from query if already present', () => {
        (useRouter as Mock).mockImplementation(() => ({
            asPath: CATEGORY_URL,
            push: mockPush,
            query: { [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({ flags: ['test-flag'] }) },
        }));

        useQueryParams().updateFilterFlags('test-flag');

        expect(mockPush).toBeCalledWith(
            {
                pathname: CATEGORY_URL,
                query: {},
            },
            undefined,
            {
                shallow: true,
            },
        );
    });
});
