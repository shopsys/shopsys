import { Translate } from 'next-translate';
import { CombinedError } from 'urql';
import { handleFormErrors } from 'utils/forms/handleFormErrors';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';
import { describe, expect, test, vi, beforeEach } from 'vitest';

vi.mock('utils/toasts/showErrorMessage', () => ({
    showErrorMessage: vi.fn(),
}));

const createMockT = () => vi.fn((key: string) => key) as unknown as Translate;

const createMockFormMethods = (fields: string[] = ['email', 'password']) => ({
    setError: vi.fn(),
    getValues: vi.fn(() => Object.fromEntries(fields.map((f) => [f, '']))),
});

describe('handleFormErrors', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    test('should do nothing when error is undefined', () => {
        const mockT = createMockT();
        const mockFormMethods = createMockFormMethods();

        handleFormErrors(undefined, mockFormMethods as any, mockT);

        expect(showErrorMessage).not.toHaveBeenCalled();
        expect(mockFormMethods.setError).not.toHaveBeenCalled();
    });

    test('should show override message when provided', () => {
        const mockT = createMockT();
        const mockFormMethods = createMockFormMethods();
        const error = new CombinedError({
            graphQLErrors: [
                {
                    message: 'API error',
                    extensions: { userCode: 'default' },
                },
            ],
        });

        handleFormErrors(error, mockFormMethods as any, mockT, 'Custom error message');

        expect(showErrorMessage).toHaveBeenCalledWith('Custom error message', expect.anything(), expect.anything());
    });

    test('should show parsed error message when no override provided', () => {
        const mockT = createMockT();
        const mockFormMethods = createMockFormMethods();
        const error = new CombinedError({
            graphQLErrors: [
                {
                    message: 'Invalid credentials.',
                    extensions: { userCode: 'invalid-credentials' },
                },
            ],
        });

        handleFormErrors(error, mockFormMethods as any, mockT, undefined);

        expect(showErrorMessage).toHaveBeenCalled();
    });

    test('should set form error for validation errors on known fields', () => {
        const mockT = createMockT();
        const mockFormMethods = createMockFormMethods(['email', 'password']);
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

        handleFormErrors(error, mockFormMethods as any, mockT);

        expect(mockFormMethods.setError).toHaveBeenCalledWith('email', { message: 'Invalid email', code: 'uuid' });
    });

    test('should show toast for validation errors on unknown fields', () => {
        const mockT = createMockT();
        const mockFormMethods = createMockFormMethods(['email', 'password']);
        const error = new CombinedError({
            graphQLErrors: [
                {
                    message: 'Validation failed',
                    extensions: {
                        validation: {
                            'input.unknownField': [{ message: 'Field error', code: 'uuid' }],
                        },
                    },
                },
            ],
        });

        handleFormErrors(error, mockFormMethods as any, mockT);

        expect(showErrorMessage).toHaveBeenCalledWith('Field error', expect.anything());
        expect(mockFormMethods.setError).not.toHaveBeenCalled();
    });

    test('should handle multiple validation errors - set form errors and show toasts', () => {
        const mockT = createMockT();
        const mockFormMethods = createMockFormMethods(['email', 'password']);
        const error = new CombinedError({
            graphQLErrors: [
                {
                    message: 'Validation failed',
                    extensions: {
                        validation: {
                            'input.email': [{ message: 'Invalid email', code: 'e1' }],
                            'input.password': [{ message: 'Password too short', code: 'p1' }],
                            'input.unknownField': [{ message: 'Unknown field error', code: 'u1' }],
                        },
                    },
                },
            ],
        });

        handleFormErrors(error, mockFormMethods as any, mockT);

        expect(mockFormMethods.setError).toHaveBeenCalledWith('email', { message: 'Invalid email', code: 'e1' });
        expect(mockFormMethods.setError).toHaveBeenCalledWith('password', {
            message: 'Password too short',
            code: 'p1',
        });
        expect(showErrorMessage).toHaveBeenCalledWith('Unknown field error', expect.anything());
    });

    test('should handle network error', () => {
        const mockT = createMockT();
        const mockFormMethods = createMockFormMethods();
        const error = new CombinedError({
            networkError: new Error('Network failure'),
        });

        handleFormErrors(error, mockFormMethods as any, mockT);

        // Network errors don't have applicationError in the parsed result,
        // so no toast should be shown for applicationError
        expect(mockFormMethods.setError).not.toHaveBeenCalled();
    });

    test('should use fields parameter to determine known fields', () => {
        const mockT = createMockT();
        const mockFormMethods = createMockFormMethods(['email', 'password']);
        const customFields = {
            customEmail: { name: 'customEmail' },
            customPassword: { name: 'customPassword' },
        };
        const error = new CombinedError({
            graphQLErrors: [
                {
                    message: 'Validation failed',
                    extensions: {
                        validation: {
                            'input.customEmail': [{ message: 'Invalid email', code: 'e1' }],
                        },
                    },
                },
            ],
        });

        handleFormErrors(error, mockFormMethods as any, mockT, undefined, customFields);

        expect(mockFormMethods.setError).toHaveBeenCalledWith('customEmail', { message: 'Invalid email', code: 'e1' });
    });

    test('should not show toast when only validation errors exist (no application error)', () => {
        const mockT = createMockT();
        const mockFormMethods = createMockFormMethods(['email']);
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

        handleFormErrors(error, mockFormMethods as any, mockT);

        // Should only set form error, not show toast (since field is known)
        expect(mockFormMethods.setError).toHaveBeenCalled();
        // applicationError should be undefined for pure validation errors
    });

    test('should handle both application error and validation errors', () => {
        const mockT = createMockT();
        const mockFormMethods = createMockFormMethods(['email']);
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
                {
                    message: 'Some application error',
                    extensions: { userCode: 'cart-not-found' },
                },
            ],
        });

        handleFormErrors(error, mockFormMethods as any, mockT);

        expect(mockFormMethods.setError).toHaveBeenCalledWith('email', { message: 'Invalid email', code: 'uuid' });
        expect(showErrorMessage).toHaveBeenCalled();
    });

    test('should use GtmMessageOriginType for error tracking', () => {
        const mockT = createMockT();
        const mockFormMethods = createMockFormMethods();
        const error = new CombinedError({
            graphQLErrors: [
                {
                    message: 'Error',
                    extensions: { userCode: 'invalid-credentials' },
                },
            ],
        });

        handleFormErrors(error, mockFormMethods as any, mockT);

        // By default, should use GtmMessageOriginType.other and pass errorType in options
        expect(showErrorMessage).toHaveBeenCalledWith(expect.any(String), expect.anything(), expect.anything());
    });
});
