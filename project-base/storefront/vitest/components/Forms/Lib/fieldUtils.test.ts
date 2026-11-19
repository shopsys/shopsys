import { getFieldStateClassName } from 'components/Forms/Lib/fieldUtils';
import { describe, expect, test } from 'vitest';

const getClassNames = (className: string) => className.split(' ');

describe('getFieldStateClassName', () => {
    test('thickens the border of a field focused by a click', () => {
        expect(getClassNames(getFieldStateClassName())).toEqual(
            expect.arrayContaining(['hover:border-input-border-hovered', 'focus:inset-ring-1']),
        );
    });

    test('keeps the error border on hover and focus', () => {
        const classNames = getClassNames(getFieldStateClassName({ hasError: true }));

        expect(classNames).toEqual(
            expect.arrayContaining(['border-input-border-error', 'hover:border-input-border-error', 'inset-ring-1']),
        );
        expect(classNames).not.toContain('hover:border-input-border-hovered');
        expect(classNames).not.toContain('focus:inset-ring-input-border-hovered');
    });

    test('shows the warning border only without an error', () => {
        const warningClassNames = getClassNames(getFieldStateClassName({ hasWarning: true }));
        const errorAndWarningClassNames = getClassNames(getFieldStateClassName({ hasError: true, hasWarning: true }));

        expect(warningClassNames).toContain('border-input-border-warning');
        expect(errorAndWarningClassNames).toContain('border-input-border-error');
        expect(errorAndWarningClassNames).not.toContain('border-input-border-warning');
    });

    test('fills the field on keyboard focus in every state', () => {
        expect(getClassNames(getFieldStateClassName({ hasError: true }))).toContain(
            'focus-keyboard:bg-focus-background',
        );
    });
});
