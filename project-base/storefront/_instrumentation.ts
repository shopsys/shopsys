import * as Sentry from '@sentry/nextjs';

export function register() {
    if (process.env.NEXT_RUNTIME === 'nodejs') {
        // this is your Sentry.init call from `sentry.server.config.js|ts`
        Sentry.init({
            dsn: process.env.SENTRY_DSN,
            environment: process.env.SENTRY_ENVIRONMENT,
            tracesSampleRate: 0.1,
        });
    }

    // This is your Sentry.init call from `sentry.edge.config.js|ts`
    if (process.env.NEXT_RUNTIME === 'edge') {
        Sentry.init({
            dsn: process.env.SENTRY_DSN,
            environment: process.env.SENTRY_ENVIRONMENT,
            tracesSampleRate: 0.1,
        });
    }
}

export const onRequestError = Sentry.captureRequestError;
