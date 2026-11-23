import { getLinkClassName } from 'components/Basic/Link/linkUtils';
import { describe, expect, test } from 'vitest';

const getClasses = (...args: Parameters<typeof getLinkClassName>) => getLinkClassName(...args).split(' ');

describe('getLinkClassName', () => {
    test('underlines a text link by default and shifts the link color on hover', () => {
        const classes = getClasses();

        expect(classes).toEqual(expect.arrayContaining(['underline', 'hover:underline']));
        expect(classes).toEqual(expect.arrayContaining(['text-link-default', 'hover:text-link-hovered']));
    });

    test('keeps the font of the surrounding text for a text link', () => {
        const classes = getClasses({ variant: 'text' });

        expect(classes).not.toEqual(expect.arrayContaining(['font-secondary']));
        expect(classes.some((className) => /^text-(xs|sm|md|lg)$/.test(className))).toBe(false);
    });

    test('gives a text action the Inter interface typography, an icon gap and a permanent underline', () => {
        const classes = getClasses({ variant: 'action' });

        expect(classes).toEqual(expect.arrayContaining(['inline-flex', 'gap-1', 'font-ui', 'text-sm']));
        expect(classes).not.toContain('font-secondary');
        expect(classes).toEqual(expect.arrayContaining(['underline', 'text-link-default']));
    });

    test('underlines a navigation link only on hover and keeps the text color', () => {
        const classes = getClasses({ variant: 'navigation' });

        expect(classes).toEqual(expect.arrayContaining(['no-underline', 'hover:underline']));
        expect(classes).toEqual(expect.arrayContaining(['text-text-default', 'hover:text-text-default']));
        expect(classes).not.toContain('text-link-default');
    });

    test('uses the link color for an accent navigation link', () => {
        const classes = getClasses({ variant: 'navigation', tone: 'accent' });

        expect(classes).toEqual(expect.arrayContaining(['no-underline', 'hover:underline']));
        expect(classes).toEqual(expect.arrayContaining(['text-link-default', 'hover:text-link-hovered']));
    });

    test.each(['text', 'navigation'] as const)('keeps a subtle %s link underlined', (variant) => {
        const classes = getClasses({ variant, tone: 'subtle' });

        expect(classes).toEqual(expect.arrayContaining(['underline', 'hover:underline']));
        expect(classes).not.toContain('no-underline');
        expect(classes).toEqual(expect.arrayContaining(['text-text-less', 'hover:text-link-hovered']));
    });

    test.each(['text', 'navigation'] as const)('uses the inverted colors for an inverted %s link', (variant) => {
        const classes = getClasses({ variant, tone: 'inverted' });

        expect(classes).toEqual(
            expect.arrayContaining(['text-link-inverted-default', 'hover:text-link-inverted-hovered']),
        );
        expect(classes).toContain('hover:underline');
    });
});
