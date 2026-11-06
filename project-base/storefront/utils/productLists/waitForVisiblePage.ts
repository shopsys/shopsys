export const waitForVisiblePage = (signal: AbortSignal): Promise<void> => {
    signal.throwIfAborted();
    if (!document.hidden) return Promise.resolve();

    return new Promise((resolve, reject) => {
        const cleanup = () => {
            document.removeEventListener('visibilitychange', onVisibilityChange);
            signal.removeEventListener('abort', onAbort);
        };
        const onVisibilityChange = () => {
            if (document.hidden) return;
            cleanup();
            resolve();
        };
        const onAbort = () => {
            cleanup();
            reject(signal.reason);
        };
        document.addEventListener('visibilitychange', onVisibilityChange);
        signal.addEventListener('abort', onAbort, { once: true });
    });
};
