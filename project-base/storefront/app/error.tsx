'use client';

import { Button } from 'components/Forms/Button/Button';
import { Webline } from 'components/Layout/Webline/Webline';
import { ErrorPage, ErrorPageTextHeading, ErrorPageTextMain } from 'components/Pages/ErrorPage/ErrorPageElements';
import { useEffect } from 'react';
import { Sentry } from 'utils/sentry';

function isNotFoundError(error: Error & { digest?: string }): boolean {
    // Check if the error is a Response with status 404
    if (error instanceof Response) {
        return error.status === 404;
    }

    // Check for Vercel's NOT_FOUND error code
    if (typeof error === 'object' && 'code' in error) {
        return error.code === 'NOT_FOUND';
    }

    // Check for Next.js not found errors
    if (error.message && error.message.includes('NEXT_NOT_FOUND')) {
        return true;
    }

    return false;
}

type RouteErrorPageProps = {
    error: Error & { digest?: string };
    reset: () => void;
};

const RouteErrorPage = ({ error, reset }: RouteErrorPageProps) => {
    useEffect(() => {
        // Only report non-404 errors to Sentry
        if (!isNotFoundError(error)) {
            Sentry.captureException(error);
        }
    }, [error]);

    const isNotFound = isNotFoundError(error);

    // For 404 errors, let the not-found.tsx handle it
    if (isNotFound) {
        // not sure about this - possible loop?
        throw error; // Re-throw to let Next.js handle with not-found.tsx
    }

    return (
        <Webline>
            <ErrorPage>
                <div className="text-center">
                    <ErrorPageTextHeading>Something went wrong!</ErrorPageTextHeading>
                    <ErrorPageTextMain>
                        We encountered an error while loading this page. Please try again.
                    </ErrorPageTextMain>

                    {process.env.NODE_ENV === 'development' && (
                        <details className="mx-auto mt-4 max-w-md rounded border bg-red-50 p-4 text-left">
                            <summary className="cursor-pointer font-medium text-red-800">
                                Error Details (Development Only)
                            </summary>
                            <pre className="mt-2 text-sm whitespace-pre-wrap text-red-700">
                                {error.message}
                                {error.digest && `\nDigest: ${error.digest}`}
                            </pre>
                        </details>
                    )}

                    <div className="mt-6 flex justify-center gap-3">
                        <Button onClick={reset}>Try Again</Button>
                        <Button variant="secondary" onClick={() => window.history.back()}>
                            Go Back
                        </Button>
                    </div>
                </div>
            </ErrorPage>
        </Webline>
    );
};

export default RouteErrorPage;
