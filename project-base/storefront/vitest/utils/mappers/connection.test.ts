import { mapConnectionEdges } from 'utils/mappers/connection';
import { describe, expect, expectTypeOf, test } from 'vitest';

describe('mapConnectionEdges', () => {
    test('preserves node identity and order while skipping missing edges and nodes', () => {
        const first = { uuid: 'first', name: 'First' };
        const second = { uuid: 'second', name: 'Second' };

        const result = mapConnectionEdges([null, { node: first }, { node: null }, { node: second }]);

        expect(result).toEqual([first, second]);
        expect(result?.[0]).toBe(first);
        expectTypeOf(result).toEqualTypeOf<{ uuid: string; name: string }[] | undefined>();
    });

    test('distinguishes an absent connection from an empty connection', () => {
        expect(mapConnectionEdges(undefined)).toBeUndefined();
        expect(mapConnectionEdges(null)).toBeUndefined();
        expect(mapConnectionEdges([])).toEqual([]);
        expect(mapConnectionEdges([null, { node: null }])).toEqual([]);
    });

    test('infers both the input and output of a custom mapper', () => {
        const result = mapConnectionEdges([{ node: { name: 'First' } }, null, { node: null }], (node) => {
            expectTypeOf(node).toEqualTypeOf<{ name: string }>();
            return node.name;
        });

        expect(result).toEqual(['First']);
        expectTypeOf(result).toEqualTypeOf<string[] | undefined>();
    });

    test('does not invent fields that the query did not select', () => {
        const result = mapConnectionEdges([{ node: { uuid: 'first' } }]);

        expectTypeOf(result).toEqualTypeOf<{ uuid: string }[] | undefined>();
        // @ts-expect-error A selected UUID does not imply that the query also selected a name.
        mapConnectionEdges<{ uuid: string; name: string }>([{ node: { uuid: 'first' } }]);
    });
});
