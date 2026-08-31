import { renderHook } from '@testing-library/react';
import { CombinedError } from 'urql';
import { useErrorHandler } from 'utils/errors/useErrorHandler';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';
import { beforeEach, describe, expect, test, vi } from 'vitest';

vi.mock('utils/i18n/useTranslationWrapper', () => ({
    default: () => ({
        t: (key: string) => key,
        lang: 'en',
    }),
}));

vi.mock('utils/toasts/showErrorMessage', () => ({
    showErrorMessage: vi.fn(),
}));

describe('useErrorHandler', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    test('keeps validation toast message even when customMessage is set', () => {
        const { result } = renderHook(() => useErrorHandler({ customMessage: 'Generic complaint error' }));

        const error = new CombinedError({
            graphQLErrors: [
                {
                    message: 'Validation failed',
                    extensions: {
                        validation: {
                            'input.nonExistingFieldForValidationToast': [
                                { message: 'Specific validation message', code: 'invalid' },
                            ],
                        },
                    },
                },
            ],
        });

        result.current(error);

        expect(showErrorMessage).toHaveBeenCalledWith(
            'Specific validation message',
            expect.anything(),
            expect.objectContaining({ errorType: 'default' }),
        );
    });

    test('keeps the mapped message of a known application error even when customMessage is set', () => {
        const { result } = renderHook(() => useErrorHandler({ customMessage: 'Generic complaint error' }));

        const error = new CombinedError({
            graphQLErrors: [
                {
                    message: 'Invalid credentials.',
                    extensions: {
                        userCode: 'invalid-credentials',
                    },
                },
            ],
        });

        result.current(error);

        expect(showErrorMessage).toHaveBeenCalledWith(
            'Invalid credentials.',
            expect.anything(),
            expect.objectContaining({ errorType: 'invalid-credentials' }),
        );
    });

    test('uses customMessage for an application error that has no message of its own', () => {
        const { result } = renderHook(() => useErrorHandler({ customMessage: 'Generic complaint error' }));

        const error = new CombinedError({
            graphQLErrors: [
                {
                    message: 'Something went wrong.',
                    extensions: {
                        userCode: 'not-a-registered-error-code',
                    },
                },
            ],
        });

        result.current(error);

        expect(showErrorMessage).toHaveBeenCalledWith(
            'Generic complaint error',
            expect.anything(),
            expect.objectContaining({ errorType: 'default' }),
        );
    });

    test('keeps both the validation message and the mapped application message when they are combined', () => {
        const { result } = renderHook(() => useErrorHandler({ customMessage: 'Generic complaint error' }));

        const error = new CombinedError({
            graphQLErrors: [
                {
                    message: 'Validation failed',
                    extensions: {
                        validation: {
                            'input.nonExistingFieldForMixedValidationToast': [
                                { message: 'Specific validation message', code: 'invalid' },
                            ],
                        },
                    },
                },
                {
                    message: 'Cart not found.',
                    extensions: {
                        userCode: 'cart-not-found',
                    },
                },
            ],
        });

        result.current(error);

        expect(showErrorMessage).toHaveBeenCalledTimes(2);
        expect(showErrorMessage).toHaveBeenNthCalledWith(
            1,
            'Specific validation message',
            expect.anything(),
            expect.objectContaining({ errorType: 'default' }),
        );
        expect(showErrorMessage).toHaveBeenNthCalledWith(
            2,
            'Cart not found.',
            expect.anything(),
            expect.objectContaining({ errorType: 'cart-not-found' }),
        );
    });
});
