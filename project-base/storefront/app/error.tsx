'use client';

import { Webline } from 'components/Layout/Webline/Webline';
import { useEffect } from 'react';
import { Sentry } from 'utils/sentry';

const ErrorPage = ({ error }: { error: Error & { digest?: string } }) => {
    console.log('☀️ error.tsx');
    useEffect(() => {
        console.log('☀️ error.tsx useEffect');
        Sentry.captureException(error);
    }, [error]);

    return (
        <Webline>
            <h1>Something went wrong!</h1>
        </Webline>
    );
};

export default ErrorPage;
