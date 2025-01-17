'use client';

import * as Sentry from '@sentry/nextjs';
import { useEffect } from 'react';

const ErrorPage = ({ error, reset }: { error: Error & { digest?: string }; reset: () => void }) => {
    useEffect(() => {
        Sentry.captureException(error);
    }, [error]);

    return (
        <html>
            <body>
                <div className="flex items-center justify-center">
                    <h2>Something went wrong!</h2>
                    <p>{error.message}</p>
                    <button onClick={() => reset()}>Try again</button>
                </div>
            </body>
        </html>
    );
};

export default ErrorPage;
