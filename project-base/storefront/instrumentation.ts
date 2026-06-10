import * as Sentry from '@sentry/nextjs';
import { getTracePropagationTargetsFromInternalEndpoint } from 'utils/sentry/tracePropagationTargets';

export function register() {
    const sentryDsn = process.env.SENTRY_DSN?.trim();

    if (!sentryDsn) {
        return;
    }

    const tracePropagationTargets = getTracePropagationTargetsFromInternalEndpoint(process.env.INTERNAL_ENDPOINT);
    const sentryConfig = {
        dsn: sentryDsn,
        environment: process.env.SENTRY_ENVIRONMENT,
        release: process.env.SENTRY_RELEASE,
        tracesSampleRate: 0.1,
        ...(tracePropagationTargets.length > 0 ? { tracePropagationTargets: tracePropagationTargets } : {}),
    };

    if (process.env.NEXT_RUNTIME === 'nodejs') {
        // this is your Sentry.init call from `sentry.server.config.js|ts`
        Sentry.init(sentryConfig);
    }

    // This is your Sentry.init call from `sentry.edge.config.js|ts`
    if (process.env.NEXT_RUNTIME === 'edge') {
        Sentry.init(sentryConfig);
    }
}

export const onRequestError = Sentry.captureRequestError;
