import type { getClient } from '@sentry/nextjs';

type SentryClient = NonNullable<ReturnType<typeof getClient>>;
type Integration = Parameters<SentryClient['addIntegration']>[0];
type Transport = NonNullable<ReturnType<SentryClient['getTransport']>>;
type Envelope = Parameters<Transport['send']>[0];

const sensitiveParameterNames = /^(hash|email|token|access_token|refresh_token|password|secret|api_key)$/i;

const isSensitiveParameter = (name: string): boolean => {
    try {
        return sensitiveParameterNames.test(decodeURIComponent(name));
    } catch {
        return true;
    }
};

const scrubString = (value: string): string =>
    value.replace(/(^|[?&#])([^?&#=\s"'<>]+)=([^?&#\s"'<>]*)/g, (match, separator, name) =>
        isSensitiveParameter(name) ? `${separator}${name}=[Filtered]` : match,
    );

const scrubJson = (value: unknown): string =>
    JSON.stringify(value, (key, item) => {
        if (isSensitiveParameter(key)) {
            return '[Filtered]';
        }

        return typeof item === 'string' ? scrubString(item) : item;
    });

const scrubRecording = async (payload: string | Uint8Array): Promise<string | Uint8Array> => {
    const encoder = new TextEncoder();
    const decoder = new TextDecoder('utf-8', { fatal: true });
    const bytes = typeof payload === 'string' ? encoder.encode(payload) : payload;
    const separator = bytes.indexOf(10);

    if (separator === -1) {
        throw new Error('Missing Sentry recording header');
    }

    const header = scrubJson(JSON.parse(decoder.decode(bytes.slice(0, separator))));
    const body = bytes.slice(separator + 1);

    if (typeof payload === 'string') {
        return `${header}\n${scrubJson(JSON.parse(decoder.decode(body)))}`;
    }

    // Sentry 11 records rrweb Meta URLs outside beforeAddRecordingEvent. Scrub the
    // complete recording, including DOM snapshots, before it reaches the transport.
    const decompressed = await new Response(
        new Blob([body]).stream().pipeThrough(new DecompressionStream('deflate')),
    ).text();
    const compressed = new Uint8Array(
        await new Response(
            new Blob([scrubJson(JSON.parse(decompressed))]).stream().pipeThrough(new CompressionStream('deflate')),
        ).arrayBuffer(),
    );
    const prefix = encoder.encode(`${header}\n`);
    const result = new Uint8Array(prefix.length + compressed.length);
    result.set(prefix);
    result.set(compressed, prefix.length);

    return result;
};

const scrubEnvelope = async (envelope: Envelope): Promise<void> => {
    envelope[0] = JSON.parse(scrubJson(envelope[0]));

    for (const item of envelope[1]) {
        if (item[0].type === 'replay_recording') {
            if (typeof item[1] !== 'string' && !(item[1] instanceof Uint8Array)) {
                throw new Error('Unknown Sentry recording format');
            }

            item[1] = await scrubRecording(item[1]);
            item[0].length = typeof item[1] === 'string' ? new TextEncoder().encode(item[1]).length : item[1].length;
        } else if (!(item[1] instanceof Uint8Array)) {
            item[1] = JSON.parse(scrubJson(item[1]));
            // An existing length describes the original payload, not the sanitized one.
            if (item[0].length !== undefined) {
                item[0].length = new TextEncoder().encode(
                    typeof item[1] === 'string' ? item[1] : JSON.stringify(item[1]),
                ).length;
            }
        }
    }
};

export const sentryUrlPrivacyIntegration = (): Integration => ({
    name: 'StorefrontUrlPrivacy',
    setup(client) {
        const transport = client.getTransport();

        if (!transport) {
            return;
        }

        const send = transport.send.bind(transport);
        const flush = transport.flush.bind(transport);
        const pending = new Set<Promise<unknown>>();

        transport.send = (envelope) => {
            const sanitized = scrubEnvelope(envelope).then(
                () => send(envelope),
                () => {
                    // Fail closed: unsupported compression or a changed SDK payload must
                    // never fall back to sending the original credentials.
                    client.recordDroppedEvent('event_processor', 'replay');
                    return {};
                },
            );
            pending.add(sanitized);
            void sanitized.then(
                () => pending.delete(sanitized),
                () => pending.delete(sanitized),
            );

            return sanitized;
        };
        transport.flush = async (timeout) => {
            let timer: ReturnType<typeof setTimeout> | undefined;
            const started = Date.now();

            try {
                const finished = await Promise.race([
                    Promise.allSettled(Array.from(pending)).then(() => true),
                    new Promise<boolean>((resolve) => {
                        if (timeout) {
                            timer = setTimeout(() => resolve(false), timeout);
                        }
                    }),
                ]);

                return finished && (await flush(timeout ? Math.max(1, timeout - (Date.now() - started)) : timeout));
            } finally {
                clearTimeout(timer);
            }
        };
    },
});
