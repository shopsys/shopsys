import * as Sentry from '@sentry/nextjs';
import getConfig from 'next/config';

const { publicRuntimeConfig } = getConfig();

const dsn: string = publicRuntimeConfig.sentryDsn;
const environment: string = publicRuntimeConfig.sentryEnvironment;
const enableFeedback: boolean = publicRuntimeConfig.sentryFeedbackEnable;
const enableReplays: boolean = publicRuntimeConfig.sentryReplaysEnable;

Sentry.init({
    dsn: dsn,
    environment: environment,
    tracesSampleRate: 0.1,
    integrations: [],
    replaysSessionSampleRate: enableReplays ? 0.1 : 0,
    replaysOnErrorSampleRate: enableReplays ? 1.0 : 0,
});

// Lazy load replay integration if enabled
if (enableReplays) {
    import('@sentry/nextjs').then((lazyLoadedSentry) => {
        Sentry.addIntegration(
            lazyLoadedSentry.replayIntegration({
                maskAllText: false,
                blockAllMedia: false,
                maskAllInputs: false,
            }),
        );
    });
}

// Lazy load feedback integration if enabled
if (enableFeedback) {
    import('@sentry/nextjs').then((lazyLoadedSentry) => {
        Sentry.addIntegration(
            lazyLoadedSentry.feedbackIntegration({
                colorScheme: 'system',
            }),
        );
    });
}

export const onRouterTransitionStart = Sentry.captureRouterTransitionStart;
