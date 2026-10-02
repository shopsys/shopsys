import {
    getMetaDescription,
    htmlToPlainText,
    META_DESCRIPTION_MAX_LENGTH,
    truncateToWholeWords,
} from 'utils/seo/getMetaDescription';
import { describe, expect, test } from 'vitest';

describe('getMetaDescription tests', () => {
    test('meta description set in the administration wins over the fallback', () => {
        expect(getMetaDescription('  Set description  ', '<p>Fallback</p>')).toBe('Set description');
    });

    test('blank meta description falls back to the plain text of the html', () => {
        expect(getMetaDescription('   ', '<p>Lorem <strong>ipsum</strong> &amp; dolor</p>')).toBe(
            'Lorem ipsum & dolor',
        );
        expect(getMetaDescription(null, '<p>Lorem</p><p>ipsum</p>')).toBe('Lorem ipsum');
    });

    test('null without any source', () => {
        expect(getMetaDescription(null, null)).toBeNull();
        expect(getMetaDescription(undefined, '<p> &nbsp; </p>')).toBeNull();
    });

    test('fallback is truncated to whole words within the limit', () => {
        const longDescription = `${'word '.repeat(40)}tail`;
        const result = getMetaDescription(null, longDescription);

        expect(result?.length).toBeLessThanOrEqual(META_DESCRIPTION_MAX_LENGTH);
        expect(result?.endsWith('word')).toBe(true);
    });
});

describe('htmlToPlainText tests', () => {
    test('tags are removed and entities decoded', () => {
        expect(htmlToPlainText('<h2>Café?</h2><p>Tom &amp; Jerry &#39;quoted&#39; &#x41;</p>')).toBe(
            "Café? Tom & Jerry 'quoted' A",
        );
    });

    test('numeric entity outside the Unicode range is kept as it is', () => {
        expect(htmlToPlainText('<p>&#1114112; &#x110000; &#x41;</p>')).toBe('&#1114112; &#x110000; A');
    });

    test('whitespace is collapsed', () => {
        expect(htmlToPlainText('  <div>\n  first\n</div> <div>second   line</div>  ')).toBe('first second line');
    });

    test('encoded tags do not come back as markup', () => {
        expect(htmlToPlainText('&lt;b&gt;bold&lt;/b&gt; &lt;img src=x onerror=alert(1)&gt; text')).toBe('bold text');
    });

    test('empty input gives empty string', () => {
        expect(htmlToPlainText(null)).toBe('');
        expect(htmlToPlainText('')).toBe('');
    });
});

describe('truncateToWholeWords tests', () => {
    test('text within the limit is untouched', () => {
        expect(truncateToWholeWords('short text', 20)).toBe('short text');
        expect(truncateToWholeWords('ten chars!', 10)).toBe('ten chars!');
    });

    test('cut happens at the last whitespace within the limit', () => {
        expect(truncateToWholeWords('lorem ipsum dolor sit amet', 14)).toBe('lorem ipsum');
        expect(truncateToWholeWords('lorem ipsum dolor sit amet', 11)).toBe('lorem ipsum');
    });

    test('single word longer than the limit is cut hard', () => {
        expect(truncateToWholeWords('supercalifragilisticexpialidocious', 10)).toBe('supercalif');
    });

    test('trailing punctuation left after the cut is removed', () => {
        expect(truncateToWholeWords('first sentence, second sentence', 17)).toBe('first sentence');
    });
});
