import { Translate } from 'next-translate';
import { CombinedError } from 'urql';
import { getUserFriendlyErrors } from 'utils/errors/friendlyErrorMessageParser';
import { describe, expect, test, vi } from 'vitest';

const createMockT = () => vi.fn((key: string) => key) as unknown as Translate;

describe('getUserFriendlyErrors', () => {
    test('should return networkError for network failures', () => {
        const mockT = createMockT();
        const error = new CombinedError({
            networkError: new Error('Network failed'),
        });

        const result = getUserFriendlyErrors(error, mockT);

        expect(result.networkError).toBeDefined();
        expect(mockT).toHaveBeenCalledWith('Could not connect to server. Check your network.');
    });

    test('should return validation errors from userError', () => {
        const mockT = createMockT();
        const error = new CombinedError({
            graphQLErrors: [
                {
                    message: 'Validation failed',
                    extensions: {
                        validation: {
                            'input.email': [{ message: 'Invalid email', code: 'uuid' }],
                        },
                    },
                },
            ],
        });

        const result = getUserFriendlyErrors(error, mockT);

        expect(result.userError?.validation).toBeDefined();
        expect(result.userError?.validation?.email).toEqual({ message: 'Invalid email', code: 'uuid' });
    });

    test('should strip input prefix from validation errors', () => {
        const mockT = createMockT();
        const error = new CombinedError({
            graphQLErrors: [
                {
                    message: 'Validation failed',
                    extensions: {
                        validation: {
                            'input.firstName': [{ message: 'Required field', code: 'not-blank' }],
                            'input.lastName': [{ message: 'Required field', code: 'not-blank' }],
                        },
                    },
                },
            ],
        });

        const result = getUserFriendlyErrors(error, mockT);

        expect(result.userError?.validation).toHaveProperty('firstName');
        expect(result.userError?.validation).toHaveProperty('lastName');
        expect(result.userError?.validation).not.toHaveProperty('input.firstName');
    });

    test('should return translated message for flash-message errors', () => {
        const mockT = createMockT();
        const error = new CombinedError({
            graphQLErrors: [
                {
                    message: 'Invalid credentials.',
                    extensions: { userCode: 'invalid-credentials' },
                },
            ],
        });

        const result = getUserFriendlyErrors(error, mockT);

        expect(result.applicationError?.type).toBe('invalid-credentials');
        expect(result.applicationError?.message).toBeDefined();
    });

    test('should return original API message for no-flash-message errors', () => {
        const mockT = createMockT();
        const error = new CombinedError({
            graphQLErrors: [
                {
                    message: 'Category with slug "electronics" not found',
                    extensions: { userCode: 'category-not-found' },
                },
            ],
        });

        const result = getUserFriendlyErrors(error, mockT);

        expect(result.applicationError?.type).toBe('category-not-found');
        expect(result.applicationError?.message).toBe('Category with slug "electronics" not found');
    });

    test('should return original message for no-log errors', () => {
        const mockT = createMockT();
        const error = new CombinedError({
            graphQLErrors: [
                {
                    message: 'Token is invalid',
                    extensions: { userCode: 'invalid-token' },
                },
            ],
        });

        const result = getUserFriendlyErrors(error, mockT);

        expect(result.applicationError?.type).toBe('invalid-token');
        expect(result.applicationError?.message).toBe('Token is invalid');
    });

    test('should return default error for unknown userCode', () => {
        const mockT = createMockT();
        const error = new CombinedError({
            graphQLErrors: [
                {
                    message: 'Some unknown error',
                    extensions: { userCode: 'totally-unknown-code' },
                },
            ],
        });

        const result = getUserFriendlyErrors(error, mockT);

        expect(result.applicationError?.type).toBe('default');
        expect(mockT).toHaveBeenCalledWith('Unknown error.');
    });

    test('should return default error when no userCode is present', () => {
        const mockT = createMockT();
        const error = new CombinedError({
            graphQLErrors: [
                {
                    message: 'Internal server error',
                },
            ],
        });

        const result = getUserFriendlyErrors(error, mockT);

        expect(result.applicationError?.type).toBe('default');
        expect(mockT).toHaveBeenCalledWith('Unknown error.');
    });

    test('should return default error for empty graphQLErrors and no networkError', () => {
        const mockT = createMockT();
        const error = new CombinedError({
            graphQLErrors: [],
        });

        const result = getUserFriendlyErrors(error, mockT);

        expect(result.applicationError?.type).toBe('default');
    });

    test('should handle cart-not-found flash-message error', () => {
        const mockT = createMockT();
        const error = new CombinedError({
            graphQLErrors: [
                {
                    message: 'Cart not found.',
                    extensions: { userCode: 'cart-not-found' },
                },
            ],
        });

        const result = getUserFriendlyErrors(error, mockT);

        expect(result.applicationError?.type).toBe('cart-not-found');
    });

    test('should handle order-not-found flash-message error', () => {
        const mockT = createMockT();
        const error = new CombinedError({
            graphQLErrors: [
                {
                    message: 'Order not found.',
                    extensions: { userCode: 'order-not-found' },
                },
            ],
        });

        const result = getUserFriendlyErrors(error, mockT);

        expect(result.applicationError?.type).toBe('order-not-found');
    });

    test('should handle access-denied flash-message error', () => {
        const mockT = createMockT();
        const error = new CombinedError({
            graphQLErrors: [
                {
                    message: 'Access denied.',
                    extensions: { userCode: 'access-denied' },
                },
            ],
        });

        const result = getUserFriendlyErrors(error, mockT);

        expect(result.applicationError?.type).toBe('access-denied');
    });

    test('should handle seo-page-not-found no-log error', () => {
        const mockT = createMockT();
        const error = new CombinedError({
            graphQLErrors: [
                {
                    message: 'SEO page not found for slug',
                    extensions: { userCode: 'seo-page-not-found' },
                },
            ],
        });

        const result = getUserFriendlyErrors(error, mockT);

        expect(result.applicationError?.type).toBe('seo-page-not-found');
        expect(result.applicationError?.message).toBe('SEO page not found for slug');
    });

    test('should handle invalid-account-or-password no-log error', () => {
        const mockT = createMockT();
        const error = new CombinedError({
            graphQLErrors: [
                {
                    message: 'Invalid account or password',
                    extensions: { userCode: 'invalid-account-or-password' },
                },
            ],
        });

        const result = getUserFriendlyErrors(error, mockT);

        expect(result.applicationError?.type).toBe('invalid-account-or-password');
        expect(result.applicationError?.message).toBe('Invalid account or password');
    });

    test('should prioritize validation errors over application errors', () => {
        const mockT = createMockT();
        const error = new CombinedError({
            graphQLErrors: [
                {
                    message: 'Validation failed',
                    extensions: {
                        validation: {
                            email: [{ message: 'Invalid email', code: 'uuid' }],
                        },
                    },
                },
                {
                    message: 'Another error',
                    extensions: { userCode: 'invalid-credentials' },
                },
            ],
        });

        const result = getUserFriendlyErrors(error, mockT);

        expect(result.userError?.validation).toBeDefined();
        expect(result.applicationError).toBeDefined();
    });

    test('should handle multiple validation errors in single GraphQL error', () => {
        const mockT = createMockT();
        const error = new CombinedError({
            graphQLErrors: [
                {
                    message: 'Validation failed',
                    extensions: {
                        validation: {
                            'input.email': [
                                { message: 'Invalid email format', code: 'email' },
                                { message: 'Email already taken', code: 'unique' },
                            ],
                            'input.password': [{ message: 'Password too short', code: 'min-length' }],
                        },
                    },
                },
            ],
        });

        const result = getUserFriendlyErrors(error, mockT);

        expect(result.userError?.validation?.email?.message).toBe('Invalid email format');
        expect(result.userError?.validation?.password?.message).toBe('Password too short');
    });
});
