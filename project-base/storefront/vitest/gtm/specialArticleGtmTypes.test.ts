import { GtmPageType } from 'gtm/enums/GtmPageType';
import { SPECIAL_ARTICLE_GTM_TYPES } from 'gtm/types/objects';
import { getSpecialArticleGtmType } from 'gtm/utils/getSpecialArticleGtmTypes';
import { describe, expect, test } from 'vitest';

describe('specialArticleGtmTypes', () => {
    describe('SPECIAL_ARTICLE_GTM_TYPES configuration', () => {
        test('should contain mappings for about us pages in different languages', () => {
            expect(SPECIAL_ARTICLE_GTM_TYPES['/about-us']).toBe(GtmPageType.about);
            expect(SPECIAL_ARTICLE_GTM_TYPES['/o-nas']).toBe(GtmPageType.about);
        });

        test('should be a frozen object to prevent accidental modifications', () => {
            expect(() => {
                (SPECIAL_ARTICLE_GTM_TYPES as any)['/test'] = GtmPageType.other;
            }).toThrow();
        });
    });

    describe('getSpecialArticleGtmType', () => {
        test('should return correct GTM type for English about us page', () => {
            const result = getSpecialArticleGtmType('/about-us');
            expect(result).toBe(GtmPageType.about);
        });

        test('should return correct GTM type for Czech about us page', () => {
            const result = getSpecialArticleGtmType('/o-nas');
            expect(result).toBe(GtmPageType.about);
        });

        test('should return null for unknown article slugs', () => {
            const result = getSpecialArticleGtmType('/unknown-article');
            expect(result).toBeNull();
        });

        test('should return null for empty slug', () => {
            const result = getSpecialArticleGtmType('');
            expect(result).toBeNull();
        });

        test('should return null for non-string parameters', () => {
            const result1 = getSpecialArticleGtmType(null as any);
            const result2 = getSpecialArticleGtmType(undefined as any);
            const result3 = getSpecialArticleGtmType(123 as any);

            expect(result1).toBeNull();
            expect(result2).toBeNull();
            expect(result3).toBeNull();
        });

        test('should handle case-sensitive slugs correctly', () => {
            const result = getSpecialArticleGtmType('/About-Us');
            expect(result).toBeNull(); // Should be case-sensitive
        });

        test('should handle slugs with trailing slashes', () => {
            const result = getSpecialArticleGtmType('/about-us/');
            expect(result).toBeNull(); // Exact match required
        });
    });
});
