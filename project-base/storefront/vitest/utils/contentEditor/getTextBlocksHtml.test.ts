import { TypeContentBlock } from 'types/contentEditor';
import { getTextBlocksHtml } from 'utils/contentEditor/getTextBlocksHtml';
import { describe, expect, test } from 'vitest';

const text = (html: string): TypeContentBlock => ({ id: html, type: 'text', data: { html } });

describe('getTextBlocksHtml', () => {
    test('returns an empty string for a missing or empty document', () => {
        expect(getTextBlocksHtml(null)).toBe('');
        expect(getTextBlocksHtml({ blocks: [] })).toBe('');
    });

    test('joins the html of text blocks, also from columns, in document order and skips other blocks', () => {
        const columns: TypeContentBlock = {
            id: 'columns',
            type: 'columns-1-1',
            data: { layout: '1/2-1/2' },
            children: [
                [text('<p>b</p>')],
                [{ id: 'products', type: 'products', data: { catnums: ['x'] } }, text('<p>c</p>')],
            ],
        };

        expect(getTextBlocksHtml({ blocks: [text('<p>a</p>'), columns, text('<p>d</p>')] })).toBe(
            '<p>a</p> <p>b</p> <p>c</p> <p>d</p>',
        );
    });
});
