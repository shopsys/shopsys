import { getErrorMessage } from 'components/Blocks/Popup/ErrorPopup';
import { FieldErrors } from 'react-hook-form';
import { describe, expect, test } from 'vitest';

describe('getErrorMessage', () => {
    describe('with errors prop', () => {
        test('returns error message when field has an error', () => {
            const errors: FieldErrors = {
                email: { type: 'required', message: 'Email is required' },
            };

            const result = getErrorMessage('email', {}, errors);

            expect(result).toBe('Email is required');
        });

        test('returns undefined when field has no error', () => {
            const errors: FieldErrors = {
                email: { type: 'required', message: 'Email is required' },
            };

            const result = getErrorMessage('password', {}, errors);

            expect(result).toBeUndefined();
        });

        test('returns undefined when message is not a string', () => {
            const errors: FieldErrors = {
                email: { type: 'required', message: undefined },
            };

            const result = getErrorMessage('email', {}, errors);

            expect(result).toBeUndefined();
        });
    });

    describe('without errors prop', () => {
        test('falls back to field.errorMessage', () => {
            const result = getErrorMessage('email', { errorMessage: 'Email is required' });

            expect(result).toBe('Email is required');
        });

        test('returns undefined when no errorMessage', () => {
            const result = getErrorMessage('email', {});

            expect(result).toBeUndefined();
        });
    });
});
