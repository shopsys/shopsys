import { getArticleHtmlHeadingAnchors } from 'utils/articleHeadingAnchors';
import { describe, expect, test } from 'vitest';

describe('getArticleHtmlHeadingAnchors', () => {
    test('returns h2 headings and adds unique ids to them', () => {
        const result = getArticleHtmlHeadingAnchors(
            '<p>Intro</p><h2>First section</h2><h2><strong>First section</strong></h2>',
        );

        expect(result.headings).toEqual([
            { id: 'first-section', title: 'First section' },
            { id: 'first-section-2', title: 'First section' },
        ]);
        expect(result.htmlWithHeadingAnchors).toBe(
            '<p>Intro</p><h2 id="first-section">First section</h2><h2 id="first-section-2"><strong>First section</strong></h2>',
        );
    });

    test('keeps the article introduction id reserved for the introduction heading', () => {
        const result = getArticleHtmlHeadingAnchors('<h2>Article introduction</h2>');

        expect(result.headings).toEqual([{ id: 'article-introduction-2', title: 'Article introduction' }]);
        expect(result.htmlWithHeadingAnchors).toBe('<h2 id="article-introduction-2">Article introduction</h2>');
    });

    test('replaces existing heading ids with ids generated from heading title', () => {
        const result = getArticleHtmlHeadingAnchors('<h2 id="custom-id">Custom heading</h2>');

        expect(result.headings).toEqual([{ id: 'custom-heading', title: 'Custom heading' }]);
        expect(result.htmlWithHeadingAnchors).toBe('<h2 id="custom-heading">Custom heading</h2>');
    });

    test('decodes entities and strips nested html from navigation titles', () => {
        const result = getArticleHtmlHeadingAnchors('<h2>Dogs &amp; <em>cats</em></h2>');

        expect(result.headings).toEqual([{ id: 'dogs-cats', title: 'Dogs & cats' }]);
    });

    test('ignores headings without a readable title', () => {
        const result = getArticleHtmlHeadingAnchors('<h2><br></h2>');

        expect(result.headings).toEqual([]);
        expect(result.htmlWithHeadingAnchors).toBe('<h2><br></h2>');
    });
});
