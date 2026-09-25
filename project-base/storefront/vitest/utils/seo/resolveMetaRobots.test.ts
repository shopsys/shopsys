import { resolveMetaRobots } from 'utils/seo/resolveMetaRobots';
import { describe, expect, test } from 'vitest';

describe('resolveMetaRobots tests', () => {
    test('SEO page robots have the highest priority', () => {
        expect(resolveMetaRobots('index, follow', 'noindex', 'noindex, nofollow')).toBe('index, follow');
    });

    test('entity robots are used when the SEO page does not define any', () => {
        expect(resolveMetaRobots(null, 'noindex', 'noindex, nofollow')).toBe('noindex');
        expect(resolveMetaRobots(undefined, 'noindex', undefined)).toBe('noindex');
    });

    test('storefront default is used only when neither SEO page nor entity define robots', () => {
        expect(resolveMetaRobots(null, null, 'noindex, nofollow')).toBe('noindex, nofollow');
        expect(resolveMetaRobots(undefined, undefined, 'noindex')).toBe('noindex');
    });

    test('null is returned when nothing defines robots', () => {
        expect(resolveMetaRobots(null, null, undefined)).toBe(null);
        expect(resolveMetaRobots('', '', undefined)).toBe(null);
    });
});
