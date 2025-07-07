import { hasValidationErrors } from 'utils/errors/hasValidationErrors';
import { describe, expect, test } from 'vitest';

describe('hasValidationErrors', () => {
    test('should return false for empty object', () => {
        const result = hasValidationErrors({});

        expect(result).toBe(false);
    });

    test('should return true for object with validation errors', () => {
        const validationErrors = {
            transport: {
                name: 'transport',
                label: 'Choose transport',
                errorMessage: 'Please select transport',
            },
        };

        const result = hasValidationErrors(validationErrors);

        expect(result).toBe(true);
    });

    test('should return true for object with multiple validation errors', () => {
        const validationErrors = {
            transport: {
                name: 'transport',
                label: 'Choose transport',
                errorMessage: 'Please select transport',
            },
            payment: {
                name: 'payment',
                label: 'Choose payment',
                errorMessage: 'Please select payment',
            },
        };

        const result = hasValidationErrors(validationErrors);

        expect(result).toBe(true);
    });

    test('should return true for object with nested properties', () => {
        const validationErrors = {
            formField: {
                nestedField: {
                    value: 'error message',
                },
            },
        };

        const result = hasValidationErrors(validationErrors);

        expect(result).toBe(true);
    });

    test('should return true for object with only undefined values (has keys)', () => {
        const validationErrors = {
            field1: undefined,
            field2: undefined,
        };

        const result = hasValidationErrors(validationErrors);

        expect(result).toBe(true);
    });

    test('should return true for object with mixed defined and undefined values', () => {
        const validationErrors = {
            field1: undefined,
            field2: {
                errorMessage: 'Some error',
            },
        };

        const result = hasValidationErrors(validationErrors);

        expect(result).toBe(true);
    });

    test('should return true for object with null values', () => {
        const validationErrors = {
            field1: null,
        };

        const result = hasValidationErrors(validationErrors);

        expect(result).toBe(true);
    });

    test('should return true for object with string values', () => {
        const validationErrors = {
            field1: 'error message',
        };

        const result = hasValidationErrors(validationErrors);

        expect(result).toBe(true);
    });

    test('should return true for object with boolean values', () => {
        const validationErrors = {
            field1: true,
            field2: false,
        };

        const result = hasValidationErrors(validationErrors);

        expect(result).toBe(true);
    });

    test('should return true for object with number values including zero', () => {
        const validationErrors = {
            field1: 0,
        };

        const result = hasValidationErrors(validationErrors);

        expect(result).toBe(true);
    });

    test('should return true for object with array values including empty arrays', () => {
        const validationErrors = {
            field1: [],
        };

        const result = hasValidationErrors(validationErrors);

        expect(result).toBe(true);
    });
});
