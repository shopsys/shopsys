import { ProductOrderingModeEnumApi } from 'graphql/generated';
import { FILTER_QUERY_PARAMETER_NAME, SORT_QUERY_PARAMETER_NAME } from 'helpers/queryParams/queryParamNames';
import { useQueryParams } from 'hooks/useQueryParams';
import { useRouter } from 'next/router';
import { useSessionStore } from 'store/zustand/useSessionStore';
import { describe, expect, Mock, test, vi } from 'vitest';

const mockSeoSensitiveFiltersGetter = vi.fn(() => ({
    SORT: true,
    AVAILABILITY: false,
    PRICE: false,
    FLAGS: true,
    PARAMETERS: {
        CHECKBOX: true,
        SLIDER: false,
    },
}));

const CATEGORY_URL = '/category-url';
const ORIGINAL_CATEGORY_URL = '/original-category-slug';
const DEFAULT_SEO_CATEGORY_PARAMETERS = new Map([
    ['default-parameter-1', new Set(['default-parameter-value-1', 'default-parameter-value-2'])],
    ['default-parameter-2', new Set(['default-parameter-value-3', 'default-parameter-value-4'])],
]);
const DEFAULT_SEO_CATEGORY_FLAGS = new Set(['default-flag-1', 'default-flag-2']);

vi.mock('helpers/filterOptions/seoCategories', async (importOriginal) => {
    const actualSeoCategoriesModule = await importOriginal<any>();

    return {
        ...actualSeoCategoriesModule,
        get SEO_SENSITIVE_FILTERS() {
            return mockSeoSensitiveFiltersGetter();
        },
    };
});

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

describe('useQueryParams().updateFilterInStock tests', () => {
    test('onlyInStock should be set to true if updating with `true`', () => {
        useQueryParams().updateFilterInStock(true);

        expect(mockPush).toBeCalledWith(
            {
                pathname: CATEGORY_URL,
                query: { [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({ onlyInStock: true }) },
            },
            undefined,
            {
                shallow: true,
            },
        );
    });

    test('onlyInStock should be set to false if updating with `false`', () => {
        (useRouter as Mock).mockImplementation(() => ({
            asPath: CATEGORY_URL,
            push: mockPush,
            query: { [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({ onlyInStock: true }) },
        }));

        useQueryParams().updateFilterInStock(false);

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

    test('onlyInStock should redirect from SEO category if it is SEO-sensitive', () => {
        // eslint-disable-next-line @typescript-eslint/ban-ts-comment
        // @ts-ignore
        mockSeoSensitiveFiltersGetter.mockImplementation(() => ({ AVAILABILITY: true }));
        (useSessionStore as unknown as Mock).mockImplementation((selector) => {
            return selector({
                defaultProductFiltersMap: {
                    sort: ProductOrderingModeEnumApi.PriceAscApi,
                    flags: DEFAULT_SEO_CATEGORY_FLAGS,
                    parameters: DEFAULT_SEO_CATEGORY_PARAMETERS,
                },
                originalCategorySlug: ORIGINAL_CATEGORY_URL,
            });
        });

        useQueryParams().updateFilterInStock(true);

        expect(mockPush).toBeCalledWith(
            {
                pathname: ORIGINAL_CATEGORY_URL,
                query: {
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                        onlyInStock: true,
                        flags: ['default-flag-1', 'default-flag-2'],
                        parameters: [
                            {
                                parameter: 'default-parameter-1',
                                values: ['default-parameter-value-1', 'default-parameter-value-2'],
                            },
                            {
                                parameter: 'default-parameter-2',
                                values: ['default-parameter-value-3', 'default-parameter-value-4'],
                            },
                        ],
                    }),
                    [SORT_QUERY_PARAMETER_NAME]: ProductOrderingModeEnumApi.PriceAscApi,
                },
            },
            undefined,
            {
                shallow: true,
            },
        );
    });

    test('onlyInStock should not redirect from SEO category if it is not SEO-sensitive', () => {
        useQueryParams().updateFilterInStock(true);

        expect(mockPush).toBeCalledWith(
            {
                pathname: CATEGORY_URL,
                query: {
                    [FILTER_QUERY_PARAMETER_NAME]: JSON.stringify({
                        onlyInStock: true,
                    }),
                },
            },
            undefined,
            {
                shallow: true,
            },
        );
    });
});
