import * as Sentry from '@sentry/nextjs';
import { beforeEach, describe, expect, test, vi } from 'vitest';

const config = vi.hoisted(() => ({
    sentryDsn: 'https://public@example.invalid/1',
    sentryFeedbackEnable: false,
    sentryReplaysEnable: false,
    domains: [{ publicGraphqlEndpoint: 'https://example.com/graphql/' }],
}));

vi.mock('envConfig', () => ({
    getPublicConfigProperty: (key: keyof typeof config) => config[key],
}));

// Keep initialization observable without sending diagnostic events to Sentry.
vi.mock('@sentry/nextjs', () => ({
    init: vi.fn(),
    addIntegration: vi.fn(),
    browserTracingIntegration: vi.fn(() => ({ name: 'BrowserTracing' })),
    thirdPartyErrorFilterIntegration: vi.fn(() => ({ name: 'ThirdPartyErrorFilter' })),
    feedbackIntegration: vi.fn(() => ({ name: 'Feedback' })),
    replayIntegration: vi.fn(() => ({ name: 'Replay' })),
    captureRouterTransitionStart: vi.fn(),
}));

describe('client instrumentation', () => {
    beforeEach(() => {
        vi.resetModules();
        config.sentryDsn = 'https://public@example.invalid/1';
        config.sentryFeedbackEnable = false;
        config.sentryReplaysEnable = false;
    });

    test.each([
        [false, false],
        [true, false],
        [false, true],
        [true, true],
    ])('preserves diagnostics with feedback=%s and replay=%s', async (feedback, replay) => {
        config.sentryFeedbackEnable = feedback;
        config.sentryReplaysEnable = replay;

        const instrumentation = await import('instrumentation-client');
        await vi.dynamicImportSettled();

        expect(Sentry.init).toHaveBeenCalledWith(
            expect.objectContaining({
                dsn: config.sentryDsn,
                tracesSampleRate: 0.1,
                replaysSessionSampleRate: replay ? 0.1 : 0,
                replaysOnErrorSampleRate: replay ? 1.0 : 0,
                integrations: [{ name: 'BrowserTracing' }, { name: 'ThirdPartyErrorFilter' }],
                tracePropagationTargets: expect.arrayContaining([expect.any(RegExp)]),
            }),
        );
        expect(instrumentation.onRouterTransitionStart).toBe(Sentry.captureRouterTransitionStart);
        expect(Sentry.addIntegration).toHaveBeenCalledTimes(Number(feedback) + Number(replay));
        expect(Sentry.feedbackIntegration).toHaveBeenCalledTimes(Number(feedback));
        expect(Sentry.replayIntegration).toHaveBeenCalledTimes(Number(replay));
        if (feedback) {
            expect(Sentry.feedbackIntegration).toHaveBeenCalledWith({ colorScheme: 'system' });
            expect(Sentry.addIntegration).toHaveBeenCalledWith({ name: 'Feedback' });
        }
        if (replay) {
            expect(Sentry.replayIntegration).toHaveBeenCalledWith({
                maskAllText: false,
                blockAllMedia: false,
                maskAllInputs: false,
            });
            expect(Sentry.addIntegration).toHaveBeenCalledWith({ name: 'Replay' });
        }
    });

    test('does not initialize integrations without a DSN', async () => {
        config.sentryDsn = '';
        config.sentryFeedbackEnable = true;
        config.sentryReplaysEnable = true;

        await import('instrumentation-client');
        await vi.dynamicImportSettled();

        expect(Sentry.init).not.toHaveBeenCalled();
        expect(Sentry.addIntegration).not.toHaveBeenCalled();
        expect(Sentry.feedbackIntegration).not.toHaveBeenCalled();
        expect(Sentry.replayIntegration).not.toHaveBeenCalled();
    });
});
