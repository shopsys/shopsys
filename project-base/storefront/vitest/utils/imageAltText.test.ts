import { getImageAlt } from 'utils/imageAltText';
import { generateProductImageAlt } from 'utils/productAltText';
import { describe, expect, test } from 'vitest';

describe('image alternative text', () => {
    test.each([null, undefined, '', ' \n\t '])('falls back to the entity name for %j', (name) => {
        expect(getImageAlt(name, 'Category')).toBe('Category');
        expect(generateProductImageAlt('Prefix Product Suffix', 'Main category', name)).toBe(
            'Main category - Prefix Product Suffix',
        );
    });

    test('prefers the administrator text over the generated product description', () => {
        expect(generateProductImageAlt('Product', 'Category', ' Side view ')).toBe('Side view');
    });

    test('uses the full product name without a dangling separator when there is no main category', () => {
        expect(generateProductImageAlt('Prefix Product Suffix', null)).toBe('Prefix Product Suffix');
    });
});
