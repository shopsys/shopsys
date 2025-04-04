import * as Sentry from '@sentry/nextjs';
import getConfig from 'next/config';

const { publicRuntimeConfig } = getConfig();

const dsn: string = publicRuntimeConfig.sentryDsn;
const environment: string = publicRuntimeConfig.sentryEnvironment;

Sentry.init({
    dsn: dsn,
    environment: environment,
    tracesSampleRate: 0.1,
});
