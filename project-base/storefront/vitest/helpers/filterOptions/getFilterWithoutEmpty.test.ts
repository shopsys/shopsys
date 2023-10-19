import { getFilterWithoutEmpty } from 'helpers/filterOptions/getFilterWithoutEmpty';
import { describe, expect, test } from 'vitest';

describe('getFilterWithoutEmpty tests', () => {
    test('undefined or null filter should return undefined', () => {
        const undefinedFilter = getFilterWithoutEmpty(undefined);
        const nullFilter = getFilterWithoutEmpty(null);

        expect(undefinedFilter).toBe(undefined);
        expect(nullFilter).toBe(undefined);
    });

    test('empty brands should be filtered out from the filter', () => {
        const filterWithoutBrands = getFilterWithoutEmpty({
            brands: [],
            flags: ['foobar'],
            parameters: [{ parameter: 'barfoo', values: ['FOO'] }],
        });

        expect(filterWithoutBrands).toStrictEqual({
            flags: ['foobar'],
            parameters: [{ parameter: 'barfoo', values: ['FOO'] }],
        });
    });

    test('empty flags should be filtered out from the filter', () => {
        const filterWithoutFlags = getFilterWithoutEmpty({
            brands: ['foobar'],
            flags: [],
            parameters: [{ parameter: 'barfoo', values: ['FOO'] }],
        });

        expect(filterWithoutFlags).toStrictEqual({
            brands: ['foobar'],
            parameters: [{ parameter: 'barfoo', values: ['FOO'] }],
        });
    });

    test('empty parameters should be filtered out from the filter', () => {
        const filterWithoutParameters = getFilterWithoutEmpty({
            brands: ['barfoo'],
            flags: ['foobar'],
            parameters: [],
        });

        expect(filterWithoutParameters).toStrictEqual({
            brands: ['barfoo'],
            flags: ['foobar'],
        });
    });
});
