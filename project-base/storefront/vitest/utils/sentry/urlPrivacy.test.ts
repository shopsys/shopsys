import { Blob as NodeBlob } from 'node:buffer';
import { deflateSync, inflateSync } from 'node:zlib';
import * as Sentry from '@sentry/nextjs';
import { sentryDataCollection } from 'utils/sentry/dataCollection';
import { sentryUrlPrivacyIntegration } from 'utils/sentry/urlPrivacy';
import { afterEach, describe, expect, test, vi } from 'vitest';

const secretUrl = 'https://shop.example/reset-password?email=privacy-test%40example.test&hash=FAKE_RESET_TOKEN&page=2';

const createClient = (rejectRequests = false) => {
    const requests: (string | Uint8Array)[] = [];
    const client = Sentry.init({
        dsn: 'https://public@sentry.invalid/1',
        defaultIntegrations: false,
        integrations: [sentryUrlPrivacyIntegration()],
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
    vi.unstubAllGlobals();
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

    test.each([false, true])('sanitizes the complete Replay envelope (compressed: %s)', async (compressed) => {
        vi.stubGlobal('Blob', NodeBlob);
        const { client, requests } = createClient();
        const recording = JSON.stringify([
            { type: 4, data: { href: secretUrl }, timestamp: 1 },
            { type: 2, data: { node: { attributes: { href: secretUrl } } }, timestamp: 2 },
            { type: 3, data: { attributes: [{ attributes: { href: secretUrl } }] }, timestamp: 3 },
            { type: 5, data: { tag: 'breadcrumb', payload: { data: { to: secretUrl } } }, timestamp: 4 },
        ]);
        const payload = compressed
            ? new Uint8Array(Buffer.concat([Buffer.from('{"segment_id":0}\n'), deflateSync(recording)]))
            : `{"segment_id":0}\n${recording}`;

        await client.sendEnvelope([
            {},
            [
                [
                    { type: 'replay_event' },
                    {
                        type: 'replay_event',
                        urls: [secretUrl],
                        error_ids: [],
                        trace_ids: [],
                        segment_names: [],
                        replay_id: '12345678901234567890123456789012',
                        segment_id: 0,
                        replay_type: 'session',
                    },
                ],
                [{ type: 'replay_recording', length: payload.length }, payload],
            ],
        ]);
        expect(await client.flush(2000)).toBe(true);

        expect(requests).toHaveLength(1);
        const envelope = Buffer.from(requests[0]);
        const lines = envelope.toString().split('\n');
        expect(lines[2]).toContain('email=[Filtered]&hash=[Filtered]');
        const prefix = `${lines.slice(0, 4).join('\n')}\n`;
        const recordingPayload = envelope.subarray(Buffer.byteLength(prefix));
        expect(JSON.parse(lines[3]).length).toBe(recordingPayload.length);
        const recordingBody = recordingPayload.subarray(recordingPayload.indexOf(10) + 1);
        const decoded = compressed ? inflateSync(recordingBody).toString() : recordingBody.toString();
        const events = JSON.parse(decoded);
        expect(events).toHaveLength(4);
        expect(decoded).not.toContain('FAKE_RESET_TOKEN');
        expect(decoded).not.toContain('privacy-test');
        expect(decoded).toContain('page=2');
    });

    test('drops the entire Replay envelope instead of sending an unreadable recording', async () => {
        const { client, requests } = createClient();

        await client.sendEnvelope([
            {},
            [
                [
                    { type: 'replay_event' },
                    {
                        type: 'replay_event',
                        urls: [secretUrl],
                        error_ids: [],
                        trace_ids: [],
                        segment_names: [],
                        replay_id: '12345678901234567890123456789012',
                        segment_id: 0,
                        replay_type: 'session',
                    },
                ],
                [{ type: 'replay_recording', length: 4 }, new Uint8Array([0, 1, 2, 3])],
            ],
        ]);

        expect(requests).toHaveLength(0);
    });

    test('does not send compressed Replay when the browser cannot sanitize it', async () => {
        vi.stubGlobal('Blob', NodeBlob);
        vi.stubGlobal('DecompressionStream', undefined);
        const { client, requests } = createClient();
        const payload = new Uint8Array(
            Buffer.concat([Buffer.from('{"segment_id":0}\n'), deflateSync(JSON.stringify([{ url: secretUrl }]))]),
        );

        await client.sendEnvelope([
            {},
            [
                [
                    { type: 'replay_event' },
                    {
                        type: 'replay_event',
                        urls: [secretUrl],
                        error_ids: [],
                        trace_ids: [],
                        segment_names: [],
                        replay_id: '12345678901234567890123456789012',
                        segment_id: 0,
                        replay_type: 'session',
                    },
                ],
                [{ type: 'replay_recording', length: payload.length }, payload],
            ],
        ]);

        expect(requests).toHaveLength(0);
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
});
