'use client';

import * as Sentry from '@sentry/nextjs';
import { Webline } from 'components/Layout/Webline/Webline';
import { useEffect } from 'react';

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
