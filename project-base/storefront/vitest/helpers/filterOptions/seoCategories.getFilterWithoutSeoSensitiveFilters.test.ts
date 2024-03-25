import { ProductOrderingModeEnum } from 'graphql/types';
import { getFilterWithoutSeoSensitiveFilters } from 'helpers/seoCategories/getFilterWithoutSeoSensitiveFilters';
import { FilterOptionsUrlQueryType } from 'types/productFilter';
import { describe, expect, test, vi } from 'vitest';

const DEFAULT_SEO_SENSITIVE_CONFIG = {
    SORT: false,
    AVAILABILITY: false,
    PRICE: false,
    FLAGS: false,
    PARAMETERS: {
        CHECKBOX: false,
        SLIDER: false,
    },
};

const mockSeoSensitiveFiltersGetter = vi.fn(() => DEFAULT_SEO_SENSITIVE_CONFIG);

vi.mock('config/constants', async (importOriginal) => {
    const actualConstantsModule = await importOriginal<any>();

    return {
        ...actualConstantsModule,
        get SEO_SENSITIVE_FILTERS() {
            return mockSeoSensitiveFiltersGetter();
        },
    };
});

vi.mock('store/useSessionStore', () => ({ useSessionStore: vi.fn() }));

const getFilterTestValue = (override = {} as FilterOptionsUrlQueryType): FilterOptionsUrlQueryType => ({
    brands: override.brands || ['foo', 'bar'],
    flags: override.flags || ['bar', 'foo'],
    minimalPrice: override.minimalPrice !== undefined ? override.minimalPrice : 100,
    maximalPrice: override.maximalPrice !== undefined ? override.maximalPrice : 1000,
    onlyInStock: override.onlyInStock !== undefined ? override.onlyInStock : true,
    parameters: override.parameters || [
        { parameter: 'foo', values: ['foov1', 'foov2'] },
        { parameter: 'bar', values: ['barv1', 'barv2'] },
        { parameter: 'foobar', minimalValue: 100, maximalValue: 1000 },
        { parameter: 'barfoo', minimalValue: 200, maximalValue: 2000 },
    ],
});

const removeKeysFromFilterTestValue = (
    testValue: FilterOptionsUrlQueryType,
    keysToRemove: (keyof FilterOptionsUrlQueryType)[],
) => {
    const updatedTestValue = { ...testValue };

    for (const key of keysToRemove) {
        delete updatedTestValue[key];
    }

    return updatedTestValue;
};

describe('seoCategories.getFilterWithoutSeoSensitiveFilters tests', () => {
    test('should return empty filter if the current filter is undefined or null', () => {
        const { filteredFilter: undefinedFilter } = getFilterWithoutSeoSensitiveFilters(undefined, null);
        const { filteredFilter: nullFilter } = getFilterWithoutSeoSensitiveFilters(null, null);

        expect(undefinedFilter).toBe(undefined);
        expect(nullFilter).toBe(undefined);
    });

    test('should set sort to undefined if sort is SEO sensitive', () => {
        mockSeoSensitiveFiltersGetter.mockImplementation(() => ({ ...DEFAULT_SEO_SENSITIVE_CONFIG, SORT: true }));

        expect(getFilterWithoutSeoSensitiveFilters({}, ProductOrderingModeEnum.PriceAsc).filteredSort).toBe(undefined);
    });

    test('should set sort to undefined if current sort is null', () => {
        expect(getFilterWithoutSeoSensitiveFilters({}, null).filteredSort).toBe(undefined);
    });

    test('should keep sort if sort is not SEO sensitive and is defined', () => {
        expect(getFilterWithoutSeoSensitiveFilters({}, ProductOrderingModeEnum.PriceAsc).filteredSort).toBe(
            ProductOrderingModeEnum.PriceAsc,
        );
    });

    test('should keep filters if they are not SEO sensitive', () => {
        const testValue = getFilterTestValue();

        expect(getFilterWithoutSeoSensitiveFilters(testValue, null).filteredFilter).toStrictEqual(testValue);
    });

    test('should remove availability if it is SEO sensitive', () => {
        mockSeoSensitiveFiltersGetter.mockImplementation(() => ({
            ...DEFAULT_SEO_SENSITIVE_CONFIG,
            AVAILABILITY: true,
        }));
        const testValue = getFilterTestValue();

        expect(getFilterWithoutSeoSensitiveFilters(testValue, null).filteredFilter).toStrictEqual(
            removeKeysFromFilterTestValue(testValue, ['onlyInStock']),
        );
    });

    test('should remove brands if they are SEO sensitive', () => {
        mockSeoSensitiveFiltersGetter.mockImplementation(() => ({ ...DEFAULT_SEO_SENSITIVE_CONFIG, BRANDS: true }));
        const testValue = getFilterTestValue();

        expect(getFilterWithoutSeoSensitiveFilters(testValue, null).filteredFilter).toStrictEqual(
            removeKeysFromFilterTestValue(testValue, ['brands']),
        );
    });

    test('should remove flags if they are SEO sensitive', () => {
        mockSeoSensitiveFiltersGetter.mockImplementation(() => ({ ...DEFAULT_SEO_SENSITIVE_CONFIG, FLAGS: true }));
        const testValue = getFilterTestValue();

        expect(getFilterWithoutSeoSensitiveFilters(testValue, null).filteredFilter).toStrictEqual(
            removeKeysFromFilterTestValue(testValue, ['flags']),
        );
    });

    test('should remove checkbox parameters if they are SEO sensitive', () => {
        mockSeoSensitiveFiltersGetter.mockImplementation(() => ({
            ...DEFAULT_SEO_SENSITIVE_CONFIG,
            PARAMETERS: { CHECKBOX: true, SLIDER: false },
        }));
        const testValue = getFilterTestValue();
        const testValueWithoutCheckboxParams = {
            ...testValue,
            parameters: testValue.parameters?.filter(
                (param) => typeof param.minimalValue === 'number' || typeof param.maximalValue === 'number',
            ),
        };

        expect(getFilterWithoutSeoSensitiveFilters(testValue, null).filteredFilter).toStrictEqual(
            testValueWithoutCheckboxParams,
        );
    });

    test('should remove slider parameters if they are SEO sensitive', () => {
        mockSeoSensitiveFiltersGetter.mockImplementation(() => ({
            ...DEFAULT_SEO_SENSITIVE_CONFIG,
            PARAMETERS: { CHECKBOX: false, SLIDER: true },
        }));
        const testValue = getFilterTestValue();
        const testValueWithoutCheckboxParams = {
            ...testValue,
            parameters: testValue.parameters?.filter((param) => param.values),
        };

        expect(getFilterWithoutSeoSensitiveFilters(testValue, null).filteredFilter).toStrictEqual(
            testValueWithoutCheckboxParams,
        );
    });

    test('should remove parameters if after removal there are none left', () => {
        mockSeoSensitiveFiltersGetter.mockImplementation(() => ({
            ...DEFAULT_SEO_SENSITIVE_CONFIG,
            PARAMETERS: { CHECKBOX: true, SLIDER: true },
        }));
        const testValue = getFilterTestValue();

        expect(getFilterWithoutSeoSensitiveFilters(testValue, null).filteredFilter).toStrictEqual(
            removeKeysFromFilterTestValue(testValue, ['parameters']),
        );
    });

    test('should remove prices if they are SEO sensitive', () => {
        mockSeoSensitiveFiltersGetter.mockImplementation(() => ({ ...DEFAULT_SEO_SENSITIVE_CONFIG, PRICE: true }));
        const testValue = getFilterTestValue();

        expect(getFilterWithoutSeoSensitiveFilters(testValue, null).filteredFilter).toStrictEqual(
            removeKeysFromFilterTestValue(testValue, ['minimalPrice', 'maximalPrice']),
        );
    });
});
