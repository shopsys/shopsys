import * as Sentry from '@sentry/nextjs';

export function register() {
    const sentryDsn = process.env.SENTRY_DSN?.trim();

    if (!sentryDsn) {
        return;
    }

    if (process.env.NEXT_RUNTIME === 'nodejs') {
        // this is your Sentry.init call from `sentry.server.config.js|ts`
        Sentry.init({
            dsn: sentryDsn,
            environment: process.env.SENTRY_ENVIRONMENT,
            tracesSampleRate: 0.1,
        });
    }

    // This is your Sentry.init call from `sentry.edge.config.js|ts`
    if (process.env.NEXT_RUNTIME === 'edge') {
        Sentry.init({
            dsn: sentryDsn,
            environment: process.env.SENTRY_ENVIRONMENT,
            tracesSampleRate: 0.1,
        });
    }
}

export const onRequestError = Sentry.captureRequestError;
