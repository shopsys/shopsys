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
    test('keeps parameter and group order while moving values with reordered products', () => {
        const phone = product([parameter('bluetooth', ['Yes'], 'Bluetooth'), parameter('warranty', ['1'], 'Warranty')]);
        const camera = product([
            { ...parameter('color', ['Black'], 'Color'), group: null },
            parameter('warranty', ['5'], 'Warranty'),
        ]);
        const original = getComparisonParameters([phone, camera]);
        const reordered = getComparisonParameters([camera, phone], [phone, camera]);

        expect(reordered.map(({ uuid, group }) => ({ uuid, group }))).toEqual(
            original.map(({ uuid, group }) => ({ uuid, group })),
        );
        expect(reordered.map(({ values }) => values)).toEqual([
            [null, ['Yes']],
            [['5'], ['1']],
            [['Black'], null],
        ]);
        expect(reordered.map(({ isDifferent }) => isDifferent)).toEqual(original.map(({ isDifferent }) => isDifferent));
    });

    test('only shows parameters belonging to the visible pair, in the original order', () => {
        const hidden = product([parameter('screen', ['27'], 'Screen')]);
        const phone = product([parameter('bluetooth', ['Yes'], 'Bluetooth')]);
        const camera = product([parameter('color', ['Black'], 'Color')]);

        const result = getComparisonParameters([camera, phone], [hidden, phone, camera]);

        expect(result.map(({ uuid }) => uuid)).toEqual(['bluetooth', 'color']);
        expect(result.map(({ values }) => values)).toEqual([
            [null, ['Yes']],
            [['Black'], null],
        ]);
    });
});
