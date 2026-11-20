import { getLinkClassName } from 'components/Basic/Link/linkUtils';
import { describe, expect, test } from 'vitest';

const getClasses = (...args: Parameters<typeof getLinkClassName>) => getLinkClassName(...args).split(' ');

describe('getLinkClassName', () => {
    test('underlines an inline link by default, thickens the underline and shifts the link color on hover', () => {
        const classes = getClasses();

        expect(classes).toEqual(expect.arrayContaining(['underline', 'hover:underline', 'hover:decoration-2']));
        expect(classes).toEqual(expect.arrayContaining(['text-link-default', 'hover:text-link-hovered']));
    });

    test('keeps the font of the sentence for an inline link', () => {
        const classes = getClasses({ placement: 'inline' });

        expect(classes).not.toEqual(expect.arrayContaining(['font-secondary']));
        expect(classes.some((className) => /^text-(xs|sm|md|lg)$/.test(className))).toBe(false);
    });

    test('gives a standalone text action one typography, an icon gap and a permanent underline', () => {
        const classes = getClasses({ placement: 'standalone' });

        expect(classes).toEqual(
            expect.arrayContaining(['inline-flex', 'gap-1', 'font-secondary', 'font-semibold', 'text-sm']),
        );
        expect(classes).toEqual(expect.arrayContaining(['underline', 'hover:decoration-2', 'text-link-default']));
    });

    test('underlines a list link only on hover and keeps the text color', () => {
        const classes = getClasses({ placement: 'list' });

        expect(classes).toEqual(expect.arrayContaining(['no-underline', 'hover:underline']));
        expect(classes).toEqual(expect.arrayContaining(['text-text-default', 'hover:text-text-default']));
        expect(classes).not.toContain('text-link-default');
    });

    test('uses the link color for an accent list link', () => {
        const classes = getClasses({ placement: 'list', tone: 'accent' });

        expect(classes).toEqual(expect.arrayContaining(['no-underline', 'hover:underline']));
        expect(classes).toEqual(expect.arrayContaining(['text-link-default', 'hover:text-link-hovered']));
    });

    test.each(['inline', 'list'] as const)('keeps a subtle %s link underlined', (placement) => {
        const classes = getClasses({ placement, tone: 'subtle' });

        expect(classes).toEqual(expect.arrayContaining(['underline', 'hover:underline']));
        expect(classes).not.toContain('no-underline');
        expect(classes).toEqual(expect.arrayContaining(['text-text-less', 'hover:text-link-hovered']));
    });

    test.each(['inline', 'list'] as const)('uses the inverted colors for an inverted %s link', (placement) => {
        const classes = getClasses({ placement, tone: 'inverted' });

        expect(classes).toEqual(
            expect.arrayContaining(['text-link-inverted-default', 'hover:text-link-inverted-hovered']),
        );
        expect(classes).toContain('hover:underline');
    });
});
