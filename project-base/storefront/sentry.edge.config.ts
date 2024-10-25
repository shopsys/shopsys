import * as Sentry from '@sentry/nextjs';

Sentry.init({
    dsn: process.env.SENTRY_DSN ?? '',
    environment: process.env.SENTRY_ENVIRONMENT ?? '',
    tracesSampleRate: 0.1,
});
