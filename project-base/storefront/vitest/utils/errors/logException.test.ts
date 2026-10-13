import { captureException } from '@sentry/nextjs';
import { logException } from 'utils/errors/logException';
import { describe, expect, test, vi } from 'vitest';

vi.mock('@sentry/nextjs', () => ({
    captureException: vi.fn(),
}));

describe('logException', () => {
    test('preserves the original exception and sends diagnostic context separately', () => {
        const error = new TypeError('Cannot read properties of null');
        const stack = error.stack;
        const extra = { location: 'ErrorPage.getInitialProps', statusCode: 500 };

        logException(error, extra);

        expect(vi.mocked(captureException).mock.calls[0][0]).toBe(error);
        expect(error.stack).toBe(stack);
        expect(captureException).toHaveBeenCalledWith(error, { extra });
    });

    test('does not carry context over to the next exception', () => {
        const firstError = new Error('First failure');
        const secondError = new Error('Second failure');

        logException(firstError, { location: 'first request' });
        logException(secondError);

        expect(captureException).toHaveBeenNthCalledWith(2, secondError, { extra: undefined });
    });

    test('preserves a rejection reason that is not an Error', () => {
        logException('Request cancelled', { location: '_app.tsx:unhandledRejection' });

        expect(captureException).toHaveBeenCalledWith('Request cancelled', {
            extra: { location: '_app.tsx:unhandledRejection' },
        });
    });
});
