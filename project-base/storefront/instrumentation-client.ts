import { getPublicConfigProperty } from 'envConfig';
import * as Sentry from '@sentry/nextjs';
import { getTracePropagationTargetsFromPublicGraphqlEndpoints as getPublicGraphqlTracePropagationTargets } from 'utils/sentry/tracePropagationTargets';

const dsn = getPublicConfigProperty('sentryDsn');
const environment = getPublicConfigProperty('sentryEnvironment');
const release = getPublicConfigProperty('sentryRelease');
const enableFeedback = getPublicConfigProperty('sentryFeedbackEnable');
const enableReplays = getPublicConfigProperty('sentryReplaysEnable');
const isSentryEnabled = dsn.trim() !== '';
const tracePropagationTargets = getPublicGraphqlTracePropagationTargets(
    getPublicConfigProperty('domains').map((domain) => domain.publicGraphqlEndpoint),
);

if (isSentryEnabled) {
    Sentry.init({
        dsn: dsn,
        environment: environment,
        release: release,
        tracesSampleRate: 0.1,
        integrations: [Sentry.browserTracingIntegration()],
        tracePropagationTargets: tracePropagationTargets,
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
}

export const onRouterTransitionStart = Sentry.captureRouterTransitionStart;
