import type { ServerResponse } from 'http';

export const isServerTimingEnabled = (): boolean => process.env.SERVER_TIMING === '1';

export const recordServerTiming = (
    response: ServerResponse | undefined,
    name: string,
    duration: number,
    cacheStatus?: 'hit' | 'miss',
): void => {
    if (!isServerTimingEnabled() || !response || response.headersSent) {
        return;
    }

    const metricName = name.replace(/[^a-zA-Z0-9_-]/g, '_');
    const metric = `${metricName};dur=${duration.toFixed(1)}${cacheStatus ? `;desc="${cacheStatus}"` : ''}`;
    const existing = response.getHeader('Server-Timing');

    response.setHeader('Server-Timing', existing ? `${existing}, ${metric}` : metric);
};

export const measureServerTiming = async <T>(
    response: ServerResponse,
    name: string,
    action: () => Promise<T>,
): Promise<T> => {
    if (!isServerTimingEnabled()) {
        return action();
    }

    const startedAt = performance.now();

    try {
        return await action();
    } finally {
        recordServerTiming(response, name, performance.now() - startedAt);
    }
};
