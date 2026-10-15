import { TypeContentDocument } from 'types/contentEditor';
import { addHeadingAnchors } from 'utils/contentEditor/addHeadingAnchors';
import { describe, expect, test } from 'vitest';

const text = (html: string) => ({ id: html, type: 'text', data: { html } });

describe('addHeadingAnchors', () => {
    test('returns no document and no headings for a missing document', () => {
        expect(addHeadingAnchors(null)).toEqual({ document: null, headings: [] });
    });

    test('adds anchors to text blocks, including nested ones, with ids unique across the document', () => {
        const document: TypeContentDocument = {
            version: 1,
            profile: 'web',
            blocks: [
                text('<h2>Intro</h2><p>x</p>'),
                { id: 'image', type: 'image', data: { url: null } },
                {
                    id: 'columns',
                    type: 'columns-1-1',
                    data: { layout: '1/2-1/2' },
                    children: [[text('<h2>Intro</h2>')], [text('<h2>Other</h2>')]],
                },
            ],
        };

        const result = addHeadingAnchors(document);

        expect(result.headings).toEqual([
            { id: 'intro', title: 'Intro' },
            { id: 'intro-2', title: 'Intro' },
            { id: 'other', title: 'Other' },
        ]);
        expect(result.document?.blocks[0].data.html).toBe('<h2 id="intro">Intro</h2><p>x</p>');
        expect(result.document?.blocks[2].children?.[0][0].data.html).toBe('<h2 id="intro-2">Intro</h2>');
        expect(result.document?.blocks[1]).toBe(document.blocks[1]);
        expect(document.blocks[0].data.html).toBe('<h2>Intro</h2><p>x</p>');
    });

    test('adds an anchor to h2 heading blocks and shares the ids with text blocks', () => {
        const heading = (text: string, level: string) => ({ id: text + level, type: 'heading', data: { text, level } });
        const document: TypeContentDocument = {
            version: 1,
            profile: 'web',
            blocks: [text('<h2>Intro</h2>'), heading('Intro', 'h2'), heading('Detail', 'h3'), heading(' ', 'h2')],
        };

        const result = addHeadingAnchors(document);

        expect(result.headings).toEqual([
            { id: 'intro', title: 'Intro' },
            { id: 'intro-2', title: 'Intro' },
        ]);
        expect(result.document?.blocks[1].data).toEqual({ text: 'Intro', level: 'h2', anchorId: 'intro-2' });
        expect(result.document?.blocks[2]).toBe(document.blocks[2]);
        expect(result.document?.blocks[3]).toBe(document.blocks[3]);
    });
});
