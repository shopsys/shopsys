import * as Sentry from '@sentry/nextjs';
import getConfig from 'next/config';

const config = getConfig();
const publicRuntimeConfig = config?.publicRuntimeConfig || {};

const dsn: string = publicRuntimeConfig.sentryDsn || '';
const environment: string = publicRuntimeConfig.sentryEnvironment || '';

if (dsn) {
    Sentry.init({
        dsn: dsn,
        environment: environment,
        tracesSampleRate: 0.1,
    });
}

export const onRouterTransitionStart = Sentry.captureRouterTransitionStart;
