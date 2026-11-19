import { getLinkClassName } from 'components/Basic/Link/linkUtils';
import { describe, expect, test } from 'vitest';

const getClasses = (...args: Parameters<typeof getLinkClassName>) => getLinkClassName(...args).split(' ');

describe('getLinkClassName', () => {
    test('underlines a content link by default and shifts its link color on hover', () => {
        const classes = getClasses();

        expect(classes).toEqual(expect.arrayContaining(['underline', 'hover:underline']));
        expect(classes).toEqual(expect.arrayContaining(['text-link-default', 'hover:text-link-hovered']));
    });

    test('underlines a navigation link only on hover and keeps the text color', () => {
        const classes = getClasses({ variant: 'navigation' });

        expect(classes).toEqual(expect.arrayContaining(['no-underline', 'hover:underline']));
        expect(classes).toEqual(expect.arrayContaining(['text-text-default', 'hover:text-text-default']));
        expect(classes).not.toContain('text-link-default');
    });

    test('uses the link color for a colored navigation link', () => {
        const classes = getClasses({ variant: 'navigation', isColored: true });

        expect(classes).toEqual(expect.arrayContaining(['no-underline', 'hover:underline']));
        expect(classes).toEqual(expect.arrayContaining(['text-link-default', 'hover:text-link-hovered']));
    });

    test.each(['content', 'navigation'] as const)('uses the inverted colors for an inverted %s link', (variant) => {
        const classes = getClasses({ variant, isInverted: true });

        expect(classes).toEqual(
            expect.arrayContaining(['text-link-inverted-default', 'hover:text-link-inverted-hovered']),
        );
        expect(classes).toContain('hover:underline');
    });
});
