'use client';

import { Webline } from 'components/Layout/Webline/Webline';
import { useEffect } from 'react';
import { Sentry } from 'utils/sentry';

const ErrorPage = ({ error, reset }: { error: Error & { digest?: string }; reset: () => void }) => {
    console.log('☀️ global-error.tsx');
    useEffect(() => {
        console.log('☀️ global-error.tsx useEffect');
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
