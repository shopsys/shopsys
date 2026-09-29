import { parse } from 'graphql';
import { describe, expect, test } from 'vitest';
import { getFields, getSelectionAt } from './graphqlSelections';

describe('GraphQL selection traversal', () => {
    test.each([
        '...ProductConnectionFields edges { node { imagesCount } }',
        'edges { node { imagesCount } } ...ProductConnectionFields',
    ])('merges repeated fields independently of selection order: %s', (selection) => {
        const document = parse(`
            query Products {
                products { ${selection} }
            }
            fragment ProductConnectionFields on ProductConnection {
                edges { node { uuid } }
                ... on ProductConnection {
                    edges { node { stockQuantity } }
                }
            }
        `);

        const fields = getFields(document, getSelectionAt(document, ['products', 'edges', 'node']));

        expect(fields.map((field) => field.name.value).sort()).toEqual(['imagesCount', 'stockQuantity', 'uuid']);
    });

    test('keeps separately aliased selections apart', () => {
        const document = parse(`
            query Products {
                compact: products { edges { node { uuid } } }
                full: products { edges { node { uuid stockQuantity } } }
            }
        `);

        const compactFields = getFields(document, getSelectionAt(document, ['compact', 'edges', 'node']));
        const fullFields = getFields(document, getSelectionAt(document, ['full', 'edges', 'node']));

        expect(compactFields.map((field) => field.name.value)).toEqual(['uuid']);
        expect(fullFields.map((field) => field.name.value)).toContain('stockQuantity');
    });

    test('reports a missing selection instead of returning an empty selection set', () => {
        const document = parse('query Products { products { uuid } }');

        expect(() => getSelectionAt(document, ['products', 'edges'])).toThrow('Missing selection products.edges');
    });
});
