import { render, screen } from '@testing-library/react';
import { ErrorBoundary, FallbackProps } from 'components/Basic/ErrorBoundary';
import { afterAll, beforeAll, describe, expect, test, vi } from 'vitest';

const ThrowError = ({ shouldThrow }: { shouldThrow: boolean }) => {
    if (shouldThrow) {
        throw new Error('Test error');
    }
    return <div>No error</div>;
};

describe('ErrorBoundary', () => {
    beforeAll(() => {
        // Suppress React error boundary console errors during tests
        vi.spyOn(globalThis.console, 'error').mockImplementation(() => undefined);
    });
    afterAll(() => {
        vi.restoreAllMocks();
    });

    test('renders children when no error', () => {
        render(
            <ErrorBoundary fallbackRender={() => <div>Error</div>}>
                <div>Content</div>
            </ErrorBoundary>,
        );

        expect(screen.getByText('Content')).toBeInTheDocument();
    });

    test('renders fallback when error occurs', () => {
        render(
            <ErrorBoundary fallbackRender={({ error }) => <div>Error: {error.message}</div>}>
                <ThrowError shouldThrow />
            </ErrorBoundary>,
        );

        expect(screen.getByText('Error: Test error')).toBeInTheDocument();
    });

    test('calls onError callback when error occurs', () => {
        const onError = vi.fn();

        render(
            <ErrorBoundary fallbackRender={() => <div>Error</div>} onError={onError}>
                <ThrowError shouldThrow />
            </ErrorBoundary>,
        );

        expect(onError).toHaveBeenCalledTimes(1);
        expect(onError).toHaveBeenCalledWith(
            expect.any(Error),
            expect.objectContaining({ componentStack: expect.any(String) }),
        );
    });

    test('resetErrorBoundary clears error state', () => {
        const FallbackWithReset = ({ error, resetErrorBoundary }: FallbackProps) => (
            <div>
                <span>Error: {error.message}</span>
                <button onClick={resetErrorBoundary}>Reset</button>
            </div>
        );

        const { rerender } = render(
            <ErrorBoundary fallbackRender={FallbackWithReset}>
                <ThrowError shouldThrow />
            </ErrorBoundary>,
        );

        expect(screen.getByText('Error: Test error')).toBeInTheDocument();

        screen.getByText('Reset').click();

        rerender(
            <ErrorBoundary fallbackRender={FallbackWithReset}>
                <ThrowError shouldThrow={false} />
            </ErrorBoundary>,
        );

        expect(screen.getByText('No error')).toBeInTheDocument();
    });

    test('does not call onError when no error', () => {
        const onError = vi.fn();

        render(
            <ErrorBoundary fallbackRender={() => <div>Error</div>} onError={onError}>
                <div>Content</div>
            </ErrorBoundary>,
        );

        expect(onError).not.toHaveBeenCalled();
    });

    test('provides error object in fallbackRender props', () => {
        const fallbackRender = vi.fn(({ error }: FallbackProps) => <div>Error: {error.message}</div>);

        render(
            <ErrorBoundary fallbackRender={fallbackRender}>
                <ThrowError shouldThrow />
            </ErrorBoundary>,
        );

        expect(fallbackRender).toHaveBeenCalledWith(
            expect.objectContaining({
                error: expect.any(Error),
                resetErrorBoundary: expect.any(Function),
            }),
        );
    });

    test('provides resetErrorBoundary function in fallbackRender props', () => {
        const fallbackRender = vi.fn(({ resetErrorBoundary }: FallbackProps) => (
            <button onClick={resetErrorBoundary}>Reset</button>
        ));

        render(
            <ErrorBoundary fallbackRender={fallbackRender}>
                <ThrowError shouldThrow />
            </ErrorBoundary>,
        );

        expect(fallbackRender).toHaveBeenCalledWith(
            expect.objectContaining({
                resetErrorBoundary: expect.any(Function),
            }),
        );
    });

    test('handles error thrown during initial render', () => {
        const ComponentWithErrorOnMount = () => {
            throw new Error('Mount error');
        };

        render(
            <ErrorBoundary fallbackRender={({ error }) => <div>Caught: {error.message}</div>}>
                <ComponentWithErrorOnMount />
            </ErrorBoundary>,
        );

        expect(screen.getByText('Caught: Mount error')).toBeInTheDocument();
    });

    test('handles multiple consecutive errors without crash', () => {
        const { rerender } = render(
            <ErrorBoundary fallbackRender={({ error }) => <div>Error: {error.message}</div>}>
                <ThrowError shouldThrow />
            </ErrorBoundary>,
        );

        expect(screen.getByText('Error: Test error')).toBeInTheDocument();

        const ThrowDifferentError = () => {
            throw new Error('Different error');
        };

        rerender(
            <ErrorBoundary fallbackRender={({ error }) => <div>Error: {error.message}</div>}>
                <ThrowDifferentError />
            </ErrorBoundary>,
        );

        expect(screen.getByText(/Error:/)).toBeInTheDocument();
    });
});
