import { DEFAULT_SORT } from 'config/constants';
import { ProductOrderingModeEnum } from 'graphql/types';
import { buildNewQueryAfterFilterChange } from 'utils/filterOptions/buildNewQueryAfterFilterChange';
import { describe, expect, test } from 'vitest';

describe('buildNewQueryAfterFilterChange tests', () => {
    test('building new query after filter change should set page and loadmore to undefined', () => {
        const newQuery = buildNewQueryAfterFilterChange(
            { filter: 'foobar', lm: '2', page: '3' },
            { brands: ['foo'], flags: ['bar'] },
            ProductOrderingModeEnum.NameAsc,
        );

        expect(newQuery).toStrictEqual({
            filter: '{"brands":["foo"],"flags":["bar"]}',
            lm: undefined,
            page: undefined,
            sort: 'NAME_ASC',
        });
    });

    test('building new query after filter change with empty filter should set the filter to undefined', () => {
        const newQuery = buildNewQueryAfterFilterChange(
            { filter: 'foobar', lm: '2', page: '3' },
            {
                onlyInStock: false,
                minimalPrice: undefined,
                maximalPrice: undefined,
                brands: [],
                flags: [],
                parameters: [],
            },
            ProductOrderingModeEnum.NameAsc,
        );

        expect(newQuery).toStrictEqual({
            filter: undefined,
            lm: undefined,
            page: undefined,
            sort: 'NAME_ASC',
        });
    });

    test('building new query with undefined new sort should keep the old sort', () => {
        const newQuery = buildNewQueryAfterFilterChange(
            { filter: 'foobar', lm: '2', page: '3', sort: ProductOrderingModeEnum.PriceAsc },
            { brands: ['foo'], flags: ['bar'] },
            undefined,
        );

        expect(newQuery).toStrictEqual({
            filter: '{"brands":["foo"],"flags":["bar"]}',
            lm: undefined,
            page: undefined,
            sort: 'PRICE_ASC',
        });
    });

    test('building new query with new sort equal to the default sort should keep the old sort', () => {
        const newQuery = buildNewQueryAfterFilterChange(
            { filter: 'foobar', lm: '2', page: '3', sort: ProductOrderingModeEnum.PriceAsc },
            { brands: ['foo'], flags: ['bar'] },
            DEFAULT_SORT,
        );

        expect(newQuery).toStrictEqual({
            filter: '{"brands":["foo"],"flags":["bar"]}',
            lm: undefined,
            page: undefined,
            sort: 'PRICE_ASC',
        });
    });
});
