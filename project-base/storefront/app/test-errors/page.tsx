'use client';

import { Button } from 'components/Forms/Button/Button';
import { useState, useEffect } from 'react';

// Test component that throws different types of errors
const ErrorTestComponent = ({ errorType }: { errorType: string }) => {
    const [asyncError, setAsyncError] = useState<Error | null>(null);

    useEffect(() => {
        if (errorType === 'network') {
            // Simulate a network error that will be caught by error boundary
            setTimeout(() => {
                setAsyncError(new Error('Network request failed - simulated API error'));
            }, 500);
        }
    }, [errorType]);

    // Throw async errors during render to trigger error boundary
    if (asyncError) {
        throw asyncError;
    }

    switch (errorType) {
        case 'render':
            throw new Error('Test render error - this should be caught by error.tsx');
        case 'network':
            return <div>Simulating network request... (will error in 500ms)</div>;
        default:
            return <div>No error</div>;
    }
};

// Component that throws an error that should bubble to global error boundary
const GlobalErrorTestComponent = () => {
    // Throw an error immediately when this component renders
    throw new Error('Global error test - this should bubble up to global-error.tsx');
};

const TestErrorsPage = () => {
    const [errorType, setErrorType] = useState<string>('');
    const [showError, setShowError] = useState(false);
    const [triggerGlobalError, setTriggerGlobalError] = useState(false);

    const triggerError = (type: string) => {
        setErrorType(type);
        setShowError(true);
    };

    const resetError = () => {
        setShowError(false);
        setErrorType('');
        setTriggerGlobalError(false);
    };

    return (
        <div className="container mx-auto p-8">
            <h1 className="mb-8 text-3xl font-bold">Error Boundary Testing</h1>

            <div className="mb-8 grid gap-4">
                <div className="rounded border bg-blue-50 p-4">
                    <h2 className="mb-4 text-xl font-semibold">Test Different Error Types</h2>
                    <div className="flex flex-wrap gap-4">
                        <Button onClick={() => triggerError('render')}>Trigger Render Error</Button>
                        <Button onClick={() => triggerError('network')}>Trigger Network Error</Button>
                        <Button variant="secondary" onClick={resetError}>
                            Reset
                        </Button>
                    </div>
                    <div className="mt-4 text-sm text-gray-600">
                        <p><strong>Render Error:</strong> Immediate error during component rendering - caught by error.tsx</p>
                        <p><strong>Network Error:</strong> Simulated API failure after delay - caught by error.tsx</p>
                    </div>
                </div>

                <div className="rounded border bg-red-50 p-4">
                    <h2 className="mb-4 text-xl font-semibold">Global Error Test</h2>
                    <div className="mb-4 text-sm text-gray-600">
                        <p className="mb-2">
                            <strong>Important:</strong> The error below will likely be caught by error.tsx, not
                            global-error.tsx.
                        </p>
                        <p className="mb-2">
                            Global errors only trigger for root layout errors, provider failures, or app-level
                            initialization errors.
                        </p>
                        <p className="mb-2">
                            To test global-error.tsx, you need to throw an error in layout.tsx or a provider
                            component.
                        </p>
                        <p>This button demonstrates the error boundary hierarchy:</p>
                    </div>
                    <div className="flex gap-2">
                        <Button
                            onClick={() => {
                                setTriggerGlobalError(true);
                            }}
                        >
                            Test Error Boundary (likely error.tsx)
                        </Button>
                    </div>
                </div>
            </div>

            <div className="rounded border bg-gray-50 p-4">
                <h3 className="mb-2 text-lg font-semibold">Current Test Status</h3>
                <p>Error Type: {errorType || 'None'}</p>
                <p>Show Error: {showError ? 'Yes' : 'No'}</p>
            </div>

            {/* This will trigger the error when showError is true */}
            {showError && <ErrorTestComponent errorType={errorType} />}

            {/* This will trigger a global error when triggerGlobalError is true */}
            {triggerGlobalError && <GlobalErrorTestComponent />}
        </div>
    );
};

export default TestErrorsPage;
