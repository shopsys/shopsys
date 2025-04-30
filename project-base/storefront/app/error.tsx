'use client';

import { Webline } from 'components/Layout/Webline/Webline';
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

    return false;
}

const ErrorPage = ({ error }: { error: Error & { digest?: string } }) => {
    console.log('☀️ error.tsx');
    useEffect(() => {
        console.log('☀️ error.tsx useEffect');
        if (!isNotFoundError(error)) {
            Sentry.captureException(error);
        }
    }, [error]);

    return (
        <Webline>
            <h1>Something went wrong!</h1>
        </Webline>
    );
};

export default ErrorPage;
