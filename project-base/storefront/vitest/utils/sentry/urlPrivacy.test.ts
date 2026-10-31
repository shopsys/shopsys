import * as Sentry from '@sentry/nextjs';
import { routes } from 'config/routes';
import { sentryDataCollection } from 'utils/sentry/dataCollection';
import { scrubSentryData, sentryFeedbackUrlPrivacyIntegration } from 'utils/sentry/urlPrivacy';
import { afterEach, describe, expect, test } from 'vitest';

const secretUrl = 'https://shop.example/reset-password?email=privacy-test%40example.test&hash=FAKE_RESET_TOKEN&page=2';

const createClient = (rejectRequests = false) => {
    const requests: (string | Uint8Array)[] = [];
    const client = Sentry.init({
        dsn: 'https://public@sentry.invalid/1',
        defaultIntegrations: false,
        integrations: [sentryFeedbackUrlPrivacyIntegration()],
        beforeSend: scrubSentryData,
        beforeSendSpan: scrubSentryData,
        tracesSampleRate: 1,
        dataCollection: sentryDataCollection,
        transport: (options: Parameters<typeof Sentry.createTransport>[0]) =>
            Sentry.createTransport(options, (request) => {
                requests.push(request.body);
                if (rejectRequests) {
                    return new Promise((_, reject) => setTimeout(() => reject(new Error('Network unavailable')), 20));
                }
                return Promise.resolve({ statusCode: 200 });
            }),
    });

    if (!client) {
        throw new Error('Sentry client was not initialized');
    }

    return { client, requests };
};

afterEach(async () => {
    await Sentry.close();
    Sentry.getCurrentScope().setClient(undefined);
});

describe('Sentry outbound URL privacy', () => {
    test('flush and close remain safe when sending telemetry fails', async () => {
        const { client, requests } = createClient(true);
        client.captureEvent({ message: 'First event' });

        await expect(client.flush(2000)).resolves.toBe(true);
        client.captureEvent({ message: 'Second event' });
        await expect(client.close(2000)).resolves.toBe(true);

        // Failed requests also produce a client_report envelope.
        expect(requests.filter((body) => typeof body === 'string' && body.includes('"type":"event"'))).toHaveLength(2);
        expect(client.getOptions().enabled).toBe(false);
    });

    test('sanitizes serialized errors, navigation breadcrumbs, and structured reset credentials', async () => {
        const { client, requests } = createClient();
        client.captureEvent({
            message: `Failed request ${secretUrl}`,
            request: { url: secretUrl },
            breadcrumbs: [{ category: 'navigation', data: { from: secretUrl, to: '/products?page=2' } }],
            extra: {
                query: { email: 'privacy-test@example.test', hash: 'FAKE_RESET_TOKEN' },
                'url.query': 'email=privacy-test%40example.test&hash=FAKE_RESET_TOKEN',
                pathWithEquals: 'https://shop.example/path=part?email=privacy-test&hash=FAKE_RESET_TOKEN',
            },
        });

        expect(await client.flush(2000)).toBe(true);

        expect(requests).toHaveLength(1);
        const body = requests[0] as string;
        expect(body).not.toContain('FAKE_RESET_TOKEN');
        expect(body).not.toContain('privacy-test');
        expect(body).toContain('/reset-password?email=[Filtered]&hash=[Filtered]&page=2');
        expect(body).toContain('/products?page=2');
    });

    test('sanitizes serialized spans while preserving non-sensitive attributes', async () => {
        const { client, requests } = createClient();
        Sentry.startSpan(
            { name: 'request', attributes: { 'http.url': secretUrl, 'http.request.method': 'GET' } },
            () => {},
        );

        await client.flush(2000);

        const bodies = requests.map((body) => String(body)).join('\n');
        expect(bodies).toContain('"type":"span"');
        expect(bodies).not.toMatch(/FAKE_RESET_TOKEN|privacy-test/);
        expect(bodies).toContain('email=[Filtered]&hash=[Filtered]&page=2');
        expect(bodies).toContain('GET');
    });

    test('sanitizes the page URL attached to serialized user feedback', async () => {
        const { client, requests } = createClient();
        Sentry.captureFeedback({ message: 'The page failed', url: secretUrl });

        await client.flush(2000);

        expect(requests).toHaveLength(1);
        expect(requests[0]).toContain('"type":"feedback"');
        expect(requests[0]).not.toMatch(/FAKE_RESET_TOKEN|privacy-test/);
        expect(requests[0]).toContain('email=[Filtered]&hash=[Filtered]&page=2');
        expect(requests[0]).toContain('The page failed');
    });

    test('recognizes duplicate, encoded, mixed-case parameters and fragment credentials', async () => {
        const { client, requests } = createClient();
        client.captureEvent({
            request: {
                url: '/reset?%68ash=FAKE_RESET_TOKEN&EMAIL=privacy-test&hash=SECOND_TOKEN#token=FRAGMENT_TOKEN',
            },
        });

        await client.flush(2000);

        expect(requests).toHaveLength(1);
        expect(requests[0]).not.toMatch(/FAKE_RESET_TOKEN|privacy-test|SECOND_TOKEN|FRAGMENT_TOKEN/);
        expect(requests[0]).toContain('%68ash=[Filtered]&EMAIL=[Filtered]&hash=[Filtered]#token=[Filtered]');
    });

    test('removes capability credentials from every localized route and query name', async () => {
        const { client, requests } = createClient();
        const urls = Array.from(new Set(routes.flatMap((domain) => Object.values(domain))))
            .filter((route) => route.includes(':'))
            .map((route) => `https://shop.example${route.replace(/:[^/]+/g, 'FAKE_PATH_SECRET')}?page=2`);
        client.captureEvent({
            request: { url: '/order-confirmation?returnHash=FAKE_RETURN&writeReviewOrderHash=FAKE_REVIEW' },
            breadcrumbs: urls.map((url) => ({ category: 'navigation', data: { to: url } })),
            extra: { orderUrlHash: 'FAKE_STRUCTURED', cartUuid: 'FAKE_CART', safe: '/products?page=2' },
        });

        await client.flush(2000);

        expect(urls.length).toBeGreaterThan(10);
        expect(requests).toHaveLength(1);
        expect(requests[0]).not.toMatch(/FAKE_/);
        expect(requests[0]).toContain('/order-confirmation?returnHash=[Filtered]&writeReviewOrderHash=[Filtered]');
        expect(requests[0]).toContain('/products?page=2');
        for (const url of urls) {
            expect(requests[0]).toContain(url.replace('FAKE_PATH_SECRET', '[Filtered]'));
        }
    });
});
