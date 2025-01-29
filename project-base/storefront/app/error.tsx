'use client';

import * as Sentry from '@sentry/nextjs';
import { Webline } from 'components/Layout/Webline/Webline';
import { isNotFoundError } from 'next/dist/client/components/not-found';
import { useEffect } from 'react';

const ErrorPage = ({ error }: { error: Error & { digest?: string } }) => {
    useEffect(() => {
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
