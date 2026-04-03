import { GraphQLError } from 'graphql';
import {
    flattenValidationErrors,
    getEffectiveErrorCode,
    getFirstValidationErrorPerField,
    hasValidationErrors,
    type ParsedGraphqlError,
    parseGraphqlError,
    parseGraphqlErrorExtensions,
    parseGraphqlErrorFromJson,
    type RawValidationErrors,
} from 'utils/errors/parseGraphqlError';
import { describe, expect, test } from 'vitest';

describe('parseGraphqlErrorExtensions', () => {
    test('should return nulls for null extensions', () => {
        const result = parseGraphqlErrorExtensions(null);

        expect(result.userCode).toBeNull();
        expect(result.errorCode).toBeNull();
        expect(result.validationErrors).toBeNull();
    });

    test('should return nulls for undefined extensions', () => {
        const result = parseGraphqlErrorExtensions(undefined);

        expect(result.userCode).toBeNull();
        expect(result.errorCode).toBeNull();
        expect(result.validationErrors).toBeNull();
    });

    test('should extract userCode from extensions', () => {
        const result = parseGraphqlErrorExtensions({ userCode: 'order-not-found' });

        expect(result.userCode).toBe('order-not-found');
    });

    test('should extract errorCode from extensions', () => {
        const result = parseGraphqlErrorExtensions({ code: 404 });

        expect(result.errorCode).toBe(404);
    });

    test('should extract string errorCode from extensions', () => {
        const result = parseGraphqlErrorExtensions({ code: 'VALIDATION_ERROR' });

        expect(result.errorCode).toBe('VALIDATION_ERROR');
    });

    test('should extract validationErrors from extensions', () => {
        const validation = {
            'input.email': [{ message: 'Invalid email', code: 'uuid-1' }],
        };
        const result = parseGraphqlErrorExtensions({ validation });

        expect(result.validationErrors).toEqual(validation);
    });

    test('should handle empty extensions object', () => {
        const result = parseGraphqlErrorExtensions({});

        expect(result.userCode).toBeNull();
        expect(result.errorCode).toBeNull();
        expect(result.validationErrors).toBeNull();
    });
});

describe('parseGraphqlError', () => {
    test('should extract userCode from GraphQL error extensions', () => {
        const graphqlError = new GraphQLError('Order not found', {
            extensions: { userCode: 'order-not-found', code: 404 },
        });

        const result = parseGraphqlError(graphqlError);

        expect(result.userCode).toBe('order-not-found');
        expect(result.errorCode).toBe(404);
        expect(result.message).toBe('Order not found');
    });

    test('should handle GraphQL error with null extensions', () => {
        const graphqlError = new GraphQLError('Error');

        const result = parseGraphqlError(graphqlError);

        expect(result.userCode).toBeNull();
        expect(result.errorCode).toBeNull();
        expect(result.validationErrors).toBeNull();
    });

    test('should extract validation errors from GraphQL error', () => {
        const validation = {
            'input.email': [{ message: 'Invalid email', code: 'uuid-1' }],
        };
        const graphqlError = new GraphQLError('Validation failed', {
            extensions: { validation },
        });

        const result = parseGraphqlError(graphqlError);

        expect(result.validationErrors).toEqual(validation);
    });

    test('should preserve original message', () => {
        const graphqlError = new GraphQLError('Custom error message', {
            extensions: { userCode: 'custom-error' },
        });

        const result = parseGraphqlError(graphqlError);

        expect(result.message).toBe('Custom error message');
    });

    test('should preserve extensions object', () => {
        const extensions = { userCode: 'test', code: 500, file: '/app/test.ts', line: 42 };
        const graphqlError = new GraphQLError('Error', { extensions });

        const result = parseGraphqlError(graphqlError);

        expect(result.extensions).toEqual(extensions);
    });
});

describe('parseGraphqlErrorFromJson', () => {
    test('should parse valid JSON GraphQL error', () => {
        const jsonString = JSON.stringify({
            message: 'Invalid credentials.',
            extensions: { userCode: 'invalid-credentials', code: 401 },
        });

        const result = parseGraphqlErrorFromJson(jsonString);

        expect(result).not.toBeNull();
        expect(result?.message).toBe('Invalid credentials.');
        expect(result?.userCode).toBe('invalid-credentials');
        expect(result?.errorCode).toBe(401);
    });

    test('should return null for invalid JSON', () => {
        const result = parseGraphqlErrorFromJson('not valid json');

        expect(result).toBeNull();
    });

    test('should return null for JSON without message property', () => {
        const result = parseGraphqlErrorFromJson(JSON.stringify({ error: 'some error' }));

        expect(result).toBeNull();
    });

    test('should return null for non-object JSON', () => {
        const result = parseGraphqlErrorFromJson(JSON.stringify('just a string'));

        expect(result).toBeNull();
    });

    test('should return null for null JSON', () => {
        const result = parseGraphqlErrorFromJson(JSON.stringify(null));

        expect(result).toBeNull();
    });

    test('should handle JSON without extensions', () => {
        const jsonString = JSON.stringify({ message: 'Error occurred' });

        const result = parseGraphqlErrorFromJson(jsonString);

        expect(result).not.toBeNull();
        expect(result?.message).toBe('Error occurred');
        expect(result?.userCode).toBeNull();
        expect(result?.extensions).toBeNull();
    });
});

describe('hasValidationErrors (from parseGraphqlError)', () => {
    test('should return false when validationErrors is null', () => {
        const error: ParsedGraphqlError = {
            message: 'Error',
            errorCode: null,
            userCode: null,
            validationErrors: null,
            extensions: null,
        };

        expect(hasValidationErrors(error)).toBe(false);
    });

    test('should return false when validationErrors is empty object', () => {
        const error: ParsedGraphqlError = {
            message: 'Error',
            errorCode: null,
            userCode: null,
            validationErrors: {},
            extensions: null,
        };

        expect(hasValidationErrors(error)).toBe(false);
    });

    test('should return true when validationErrors has entries', () => {
        const error: ParsedGraphqlError = {
            message: 'Validation failed',
            errorCode: null,
            userCode: null,
            validationErrors: {
                email: [{ message: 'Invalid', code: 'uuid' }],
            },
            extensions: null,
        };

        expect(hasValidationErrors(error)).toBe(true);
    });
});

describe('flattenValidationErrors', () => {
    test('should flatten validation errors into array', () => {
        const validationErrors: RawValidationErrors = {
            email: [{ message: 'Invalid email', code: 'email-invalid' }],
            password: [{ message: 'Too short', code: 'min-length' }],
        };

        const result = flattenValidationErrors(validationErrors);

        expect(result).toHaveLength(2);
        expect(result).toContainEqual({ field: 'email', message: 'Invalid email', code: 'email-invalid' });
        expect(result).toContainEqual({ field: 'password', message: 'Too short', code: 'min-length' });
    });

    test('should handle multiple errors per field', () => {
        const validationErrors: RawValidationErrors = {
            email: [
                { message: 'Invalid email', code: 'email-invalid' },
                { message: 'Already taken', code: 'unique' },
            ],
        };

        const result = flattenValidationErrors(validationErrors);

        expect(result).toHaveLength(2);
        expect(result[0]).toEqual({ field: 'email', message: 'Invalid email', code: 'email-invalid' });
        expect(result[1]).toEqual({ field: 'email', message: 'Already taken', code: 'unique' });
    });

    test('should strip input prefix when option enabled', () => {
        const validationErrors: RawValidationErrors = {
            'input.email': [{ message: 'Invalid', code: 'uuid' }],
        };

        const result = flattenValidationErrors(validationErrors, { stripInputPrefix: true });

        expect(result[0].field).toBe('email');
    });

    test('should preserve input prefix when option disabled', () => {
        const validationErrors: RawValidationErrors = {
            'input.email': [{ message: 'Invalid', code: 'uuid' }],
        };

        const result = flattenValidationErrors(validationErrors, { stripInputPrefix: false });

        expect(result[0].field).toBe('input.email');
    });

    test('should handle empty validation errors', () => {
        const result = flattenValidationErrors({});

        expect(result).toEqual([]);
    });

    test('should handle field with empty errors array', () => {
        const validationErrors: RawValidationErrors = {
            email: [],
        };

        const result = flattenValidationErrors(validationErrors);

        expect(result).toEqual([]);
    });
});

describe('getFirstValidationErrorPerField', () => {
    test('should return first error per field', () => {
        const validationErrors: RawValidationErrors = {
            email: [
                { message: 'First error', code: '1' },
                { message: 'Second error', code: '2' },
            ],
        };

        const result = getFirstValidationErrorPerField(validationErrors, { stripInputPrefix: false });

        expect(result.email.message).toBe('First error');
        expect(result.email.code).toBe('1');
    });

    test('should strip input prefix when option enabled', () => {
        const validationErrors: RawValidationErrors = {
            'input.email': [{ message: 'Error', code: '1' }],
        };

        const result = getFirstValidationErrorPerField(validationErrors, { stripInputPrefix: true });

        expect(result).toHaveProperty('email');
        expect(result).not.toHaveProperty('input.email');
    });

    test('should handle multiple fields', () => {
        const validationErrors: RawValidationErrors = {
            email: [{ message: 'Email error', code: 'e1' }],
            password: [{ message: 'Password error', code: 'p1' }],
        };

        const result = getFirstValidationErrorPerField(validationErrors);

        expect(result.email.message).toBe('Email error');
        expect(result.password.message).toBe('Password error');
    });

    test('should skip fields with empty errors array', () => {
        const validationErrors: RawValidationErrors = {
            email: [],
            password: [{ message: 'Password error', code: 'p1' }],
        };

        const result = getFirstValidationErrorPerField(validationErrors);

        expect(result).not.toHaveProperty('email');
        expect(result.password.message).toBe('Password error');
    });

    test('should return empty object for empty input', () => {
        const result = getFirstValidationErrorPerField({});

        expect(result).toEqual({});
    });
});

describe('getEffectiveErrorCode', () => {
    test('should return errorCode when available', () => {
        const error: ParsedGraphqlError = {
            message: 'Error',
            errorCode: 404,
            userCode: 'not-found',
            validationErrors: null,
            extensions: null,
        };

        expect(getEffectiveErrorCode(error)).toBe(404);
    });

    test('should fall back to userCode when errorCode is null', () => {
        const error: ParsedGraphqlError = {
            message: 'Error',
            errorCode: null,
            userCode: 'not-found',
            validationErrors: null,
            extensions: null,
        };

        expect(getEffectiveErrorCode(error)).toBe('not-found');
    });

    test('should return null when both errorCode and userCode are null', () => {
        const error: ParsedGraphqlError = {
            message: 'Error',
            errorCode: null,
            userCode: null,
            validationErrors: null,
            extensions: null,
        };

        expect(getEffectiveErrorCode(error)).toBeNull();
    });
});
