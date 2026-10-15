import { TypeContentBlock } from 'types/contentEditor';
import { collectProductCatnums } from 'utils/contentEditor/collectProductCatnums';
import { describe, expect, test } from 'vitest';

const products = (catnums: string[]): TypeContentBlock => ({ id: catnums.join(), type: 'products', data: { catnums } });

describe('collectProductCatnums', () => {
    test('returns nothing for a missing or empty document', () => {
        expect(collectProductCatnums(null)).toEqual([]);
        expect(collectProductCatnums({ blocks: [] })).toEqual([]);
    });

    test('collects catnums from nested columns in document order', () => {
        const columns: TypeContentBlock = {
            id: 'columns',
            type: 'columns-1-1',
            data: { layout: '1/2-1/2' },
            children: [[products(['b'])], [{ id: 'text', type: 'text', data: { html: '' } }, products(['c'])]],
        };

        expect(collectProductCatnums({ blocks: [products(['a']), columns, products(['d'])] })).toEqual([
            'a',
            'b',
            'c',
            'd',
        ]);
    });

    test('removes duplicates and keeps the first occurrence', () => {
        expect(collectProductCatnums({ blocks: [products(['a', 'b']), products(['b', 'a', 'c'])] })).toEqual([
            'a',
            'b',
            'c',
        ]);
    });
});
