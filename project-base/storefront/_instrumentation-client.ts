import * as Sentry from '@sentry/nextjs';

// TODO: how to solve NEXT_PUBLIC_?
Sentry.init({
    // dsn: NEXT_PUBLIC_SENTRY_DSN,
    dsn: 'https://cfc57a465998086a7787195827b62d9c@sentry.shopsys.cloud/32',
    // environment: NEXT_PUBLIC_SENTRY_ENVIRONMENT,
    environment: 'heca_frantisek_ssfw_approuter',
    tracesSampleRate: 0.1,

    // remove, if you don't want replays
    integrations: [Sentry.replayIntegration()],
    replaysSessionSampleRate: 0.1,
    replaysOnErrorSampleRate: 1.0,
});

export const onRouterTransitionStart = Sentry.captureRouterTransitionStart;
