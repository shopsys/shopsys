import * as Sentry from '@sentry/nextjs';
import getConfig from 'next/config';

const { publicRuntimeConfig } = getConfig();

const dsn: string = publicRuntimeConfig.sentryDsn;
const environment: string = publicRuntimeConfig.sentryEnvironment;

Sentry.init({
    dsn: dsn,
    environment: environment,
    tracesSampleRate: 0.1,

    // remove, if you don't want replays
    integrations: [Sentry.replayIntegration()],
    replaysSessionSampleRate: 0.1,
    replaysOnErrorSampleRate: 1.0,
});
