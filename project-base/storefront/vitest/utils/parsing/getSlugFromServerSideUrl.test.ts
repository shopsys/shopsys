import { getSlugFromServerSideUrl } from 'utils/parsing/getSlugFromServerSideUrl';
import { describe, expect, test } from 'vitest';

describe('getSlugFromServerSideUrl', () => {
    describe('full page request (without x-nextjs-data)', () => {
        const fullPageHeaders = {};

        test('extracts slug from simple URL', () => {
            expect(getSlugFromServerSideUrl('/my-category', fullPageHeaders)).toBe('my-category');
        });

        test('extracts slug from URL with query parameters', () => {
            expect(getSlugFromServerSideUrl('/my-category?page=2&sort=name', fullPageHeaders)).toBe('my-category');
        });

        test('strips leading slash', () => {
            expect(getSlugFromServerSideUrl('/some-slug', fullPageHeaders)).toBe('some-slug');
        });

        test('returns empty string for empty URL', () => {
            expect(getSlugFromServerSideUrl('', fullPageHeaders)).toBe('');
        });

        test('extracts slug from URL with path segments', () => {
            expect(getSlugFromServerSideUrl('/categories/my-category', fullPageHeaders)).toBe('categories/my-category');
        });

        test('extracts slug from URL with path segments and query params', () => {
            expect(getSlugFromServerSideUrl('/categories/my-category?page=2', fullPageHeaders)).toBe(
                'categories/my-category',
            );
        });
    });

    describe('client-side navigation (x-nextjs-data: 1)', () => {
        const csrHeaders = { 'x-nextjs-data': '1' as string | string[] | undefined };

        test('extracts slug from Next.js data URL format', () => {
            expect(getSlugFromServerSideUrl('/_next/data/BUILD_ID/my-category.json', csrHeaders)).toBe('my-category');
        });

        test('extracts slug from data URL with query parameters', () => {
            expect(getSlugFromServerSideUrl('/_next/data/BUILD_ID/my-slug.json?page=2', csrHeaders)).toBe('my-slug');
        });

        test('extracts slug with hyphens', () => {
            expect(getSlugFromServerSideUrl('/_next/data/BUILD_ID/my-long-slug-name.json', csrHeaders)).toBe(
                'my-long-slug-name',
            );
        });

        test('extracts last segment from nested path', () => {
            expect(getSlugFromServerSideUrl('/_next/data/BUILD_ID/categories/my-category.json', csrHeaders)).toBe(
                'my-category',
            );
        });
    });
});
