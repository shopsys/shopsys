import { TypeProductInProductListFragment } from 'graphql/requests/productLists/fragments/ProductInProductListFragment.generated';
import { getComparisonParameters } from 'utils/productLists/comparison/getComparisonParameters';
import { describe, expect, test } from 'vitest';

type Parameter = TypeProductInProductListFragment['parameters'][number];
const parameter = (uuid: string, values: string[], name = 'Connection'): Parameter =>
    ({ uuid, name, group: 'Connectivity', unit: null, values: values.map((text) => ({ text })) }) as Parameter;
const product = (parameters: Parameter[]): TypeProductInProductListFragment =>
    ({ parameters }) as TypeProductInProductListFragment;

describe('getComparisonParameters', () => {
    test('keeps all values and treats a different value order as equal', () => {
        const result = getComparisonParameters([
            product([parameter('ports', ['USB', 'HDMI'])]),
            product([parameter('ports', ['HDMI', 'USB'])]),
        ]);

        expect(result[0].values).toEqual([
            ['USB', 'HDMI'],
            ['HDMI', 'USB'],
        ]);
        expect(result[0].isDifferent).toBe(false);
    });

    test('keeps missing values in the correct product column and distinguishes them from No', () => {
        const result = getComparisonParameters([product([]), product([parameter('ports', ['No'])]), product([])]);

        expect(result[0].values).toEqual([null, ['No'], null]);
        expect(result[0].isDifferent).toBe(true);
    });

    test('does not merge unrelated parameters with the same display name', () => {
        const result = getComparisonParameters([
            product([parameter('input', ['USB'])]),
            product([parameter('output', ['HDMI'])]),
        ]);

        expect(result).toHaveLength(2);
        expect(result[0].values).toEqual([['USB'], null]);
        expect(result[1].values).toEqual([null, ['HDMI']]);
        expect(result[0].group).toBe('Connectivity');
    });

    test('handles empty values and a single product without inventing differences', () => {
        const result = getComparisonParameters([product([parameter('ports', [])])]);

        expect(result[0].values).toEqual([null]);
        expect(result[0].isDifferent).toBe(false);
        expect(getComparisonParameters([])).toEqual([]);
    });
});
