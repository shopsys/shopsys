import * as Sentry from '@sentry/nextjs';
import { ErrorInfo } from 'react';

export const logErrorBoundary = (error: Error, info: ErrorInfo): void => {
    Sentry.withScope((scope) => {
        scope.setExtra('location', '_app.tsx:ErrorBoundary');
        scope.setExtra('componentStack', info.componentStack);
        scope.setExtra('digest', info.digest);
        Sentry.captureException(error);
    });
};
