import { getDocumentTitle } from 'utils/seo/getDocumentTitle';
import { describe, expect, test } from 'vitest';

describe('getDocumentTitle tests', () => {
    test('title and suffix are joined with a space', () => {
        expect(getDocumentTitle('Cart', '| Demo eshop')).toBe('Cart | Demo eshop');
    });

    test('empty parts are omitted', () => {
        expect(getDocumentTitle('Cart', '')).toBe('Cart');
        expect(getDocumentTitle('', '| Demo eshop')).toBe('| Demo eshop');
        expect(getDocumentTitle('', '')).toBe('');
    });
});
