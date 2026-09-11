import { isNoindexMetaRobots } from 'utils/seo/isNoindexMetaRobots';
import { describe, expect, test } from 'vitest';

describe('isNoindexMetaRobots tests', () => {
    test('noindex directive is detected regardless of other directives, spacing and letter case', () => {
        expect(isNoindexMetaRobots('noindex')).toBe(true);
        expect(isNoindexMetaRobots('noindex, follow')).toBe(true);
        expect(isNoindexMetaRobots('noindex,nofollow')).toBe(true);
        expect(isNoindexMetaRobots('follow, NOINDEX')).toBe(true);
    });

    test('indexable or missing robots are not noindex', () => {
        expect(isNoindexMetaRobots('index, follow')).toBe(false);
        expect(isNoindexMetaRobots('nofollow')).toBe(false);
        expect(isNoindexMetaRobots('')).toBe(false);
        expect(isNoindexMetaRobots(null)).toBe(false);
        expect(isNoindexMetaRobots(undefined)).toBe(false);
    });
});
