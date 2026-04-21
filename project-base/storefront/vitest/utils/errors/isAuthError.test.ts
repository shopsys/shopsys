import { CombinedError } from 'urql';
import { isAuthError } from 'utils/errors/isAuthError';
import { describe, expect, test } from 'vitest';

describe('isAuthError', () => {
    test('should return false for undefined error', () => {
        expect(isAuthError(undefined)).toBe(false);
    });

    test('should return true for HTTP 401 responses', () => {
        const error = new CombinedError({
            response: new Response(null, { status: 401 }),
        });

        expect(isAuthError(error)).toBe(true);
    });

    test('should return true for expired-token graphql errors', () => {
        const error = new CombinedError({
            response: new Response(null, { status: 200 }),
            graphQLErrors: [
                {
                    message: 'Token is expired. Please renew.',
                    extensions: { userCode: 'expired-token' },
                },
            ],
        });

        expect(isAuthError(error)).toBe(true);
    });

    test('should return true for invalid-token graphql errors', () => {
        const error = new CombinedError({
            response: new Response(null, { status: 200 }),
            graphQLErrors: [
                {
                    message: 'Token is not valid.',
                    extensions: { userCode: 'invalid-token' },
                },
            ],
        });

        expect(isAuthError(error)).toBe(true);
    });

    test('should return false for non-auth graphql errors', () => {
        const error = new CombinedError({
            response: new Response(null, { status: 200 }),
            graphQLErrors: [
                {
                    message: 'Category not found.',
                    extensions: { userCode: 'category-not-found' },
                },
            ],
        });

        expect(isAuthError(error)).toBe(false);
    });
});
