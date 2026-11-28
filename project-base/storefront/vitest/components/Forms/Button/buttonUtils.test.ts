import { getButtonClassName } from 'components/Forms/Button/buttonUtils';
import { describe, expect, test } from 'vitest';

const getClasses = (...args: Parameters<typeof getButtonClassName>) => getButtonClassName(...args).split(' ');

describe('getButtonClassName', () => {
    test('shows hover and active states on a regular button', () => {
        const classes = getClasses('primary', 'medium', false);

        expect(classes).toContain('hover:bg-button-primary-bg-hovered');
        expect(classes).toContain('active:bg-button-primary-bg-active');
        expect(classes).not.toContain('bg-button-primary-bg-disabled');
    });

    test('replaces hover and active states with the disabled look', () => {
        const classes = getClasses('primary', 'medium', true);

        expect(classes).toContain('bg-button-primary-bg-disabled');
        expect(classes).not.toContain('hover:bg-button-primary-bg-hovered');
        expect(classes).not.toContain('active:bg-button-primary-bg-active');
    });

    test('shows the disabled cursor only on a natively disabled button', () => {
        const classes = getClasses('primary', 'medium', true);

        expect(classes).toContain('disabled:cursor-no-drop');
        expect(classes).not.toContain('cursor-no-drop');
        expect(classes).toContain('cursor-pointer');
    });
});
