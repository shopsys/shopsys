'use client';

import * as Sentry from '@sentry/nextjs';
import { Webline } from 'components/Layout/Webline/Webline';
import { useEffect } from 'react';

const ErrorPage = ({ error, reset }: { error: Error & { digest?: string }; reset: () => void }) => {
    useEffect(() => {
        Sentry.captureException(error);
    }, [error]);

    return (
        <html>
            <body>
                <Webline className="flex items-center justify-center">
                    <h1>Something went wrong globally!</h1>
                    <p>{error.message}</p>
                    <button onClick={() => reset()}>Try again</button>
                </Webline>
            </body>
        </html>
    );
};

export default ErrorPage;
