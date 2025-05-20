import * as Sentry from '@sentry/nextjs';
import getConfig from 'next/config';

const { publicRuntimeConfig } = getConfig();

const dsn: string = publicRuntimeConfig.sentryDsn;
const environment: string = publicRuntimeConfig.sentryEnvironment;
const enableFeedback: boolean = publicRuntimeConfig.sentryFeedbackEnable;

Sentry.init({
    dsn: dsn,
    environment: environment,
    tracesSampleRate: 0.1,

    // remove, if you don't want replays
    integrations: [
        Sentry.replayIntegration({
            maskAllText: false,
            blockAllMedia: false,
            maskAllInputs: false,
        }),
    ],
    replaysSessionSampleRate: 0.1,
    replaysOnErrorSampleRate: 1.0,
});

if (enableFeedback) {
    Sentry.addIntegration(
        Sentry.feedbackIntegration({
            colorScheme: 'system',
        }),
    );
}

export const onRouterTransitionStart = Sentry.captureRouterTransitionStart;
