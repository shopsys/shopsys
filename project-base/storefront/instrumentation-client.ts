import * as Sentry from '@sentry/nextjs';
import { getPublicConfigProperty } from 'utils/config/getNextConfig';

const dsn = getPublicConfigProperty('sentryDsn', '');
const environment = getPublicConfigProperty('sentryEnvironment', '');
const enableFeedback = getPublicConfigProperty('sentryFeedbackEnable', false);
const enableReplays = getPublicConfigProperty('sentryReplaysEnable', false);

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
