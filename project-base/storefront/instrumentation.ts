import * as Sentry from '@sentry/nextjs';
import { PHASE_PRODUCTION_BUILD } from 'next/constants';
import { sentryDataCollection } from 'utils/sentry/dataCollection';
import { getTracePropagationTargetsFromInternalEndpoint } from 'utils/sentry/tracePropagationTargets';
import { scrubSentryData } from 'utils/sentry/urlPrivacy';

const REDIS_STARTUP_WAIT_MS = 2000;

export async function register() {
    const sentryDsn = process.env.SENTRY_DSN?.trim();

    if (sentryDsn) {
        const tracePropagationTargets = getTracePropagationTargetsFromInternalEndpoint(process.env.INTERNAL_ENDPOINT);
        const sentryConfig = {
            dsn: sentryDsn,
            dataCollection: sentryDataCollection,
            beforeSend: scrubSentryData,
            beforeSendSpan: scrubSentryData,
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

    if (
        process.env.NEXT_RUNTIME === 'nodejs' &&
        process.env.NEXT_PHASE !== PHASE_PRODUCTION_BUILD &&
        process.env.REDIS_HOST?.trim()
    ) {
        // Next.js awaits registration before serving requests; cache outages must still allow startup.
        const { getRedisClient } = await import('utils/redis/redisClient');
        await getRedisClient(undefined, REDIS_STARTUP_WAIT_MS);
    }
}

export const onRequestError = Sentry.captureRequestError;
