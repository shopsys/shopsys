'use client';

import * as Sentry from '@sentry/nextjs';
import { isNotFoundError } from 'next/dist/client/components/not-found';
import { useEffect } from 'react';

export default function ErrorPage({ error }: { error: Error & { digest?: string } }) {
    useEffect(() => {
        if (!isNotFoundError(error)) {
            Sentry.captureException(error);
        }
    }, [error]);

    return (
        <div>
            <h2>Something went wrong!</h2>
        </div>
    );
}
