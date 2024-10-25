import * as Sentry from '@sentry/nextjs';

Sentry.init({
    dsn: process.env.SENTRY_DSN ?? '',
    environment: process.env.SENTRY_ENVIRONMENT ?? '',
    tracesSampleRate: 0.1,

    // remove, if you don't want replays
    integrations: [Sentry.replayIntegration()],
    replaysSessionSampleRate: 0.1,
    replaysOnErrorSampleRate: 1.0,
});

export const onRouterTransitionStart = Sentry.captureRouterTransitionStart;
