const AUTH_TIMEOUT = 10_000;

const withAuthTimeout = async <T>(action: (signal: AbortSignal) => Promise<T>, message: string): Promise<T> => {
    const controller = new AbortController();
    let timeout: ReturnType<typeof setTimeout> | undefined;

    try {
        return await Promise.race([
            action(controller.signal),
            new Promise<never>((_resolve, reject) => {
                timeout = setTimeout(() => {
                    controller.abort();
                    reject(new Error(message));
                }, AUTH_TIMEOUT);
            }),
        ]);
    } finally {
        clearTimeout(timeout);
    }
};

export const withAuthLock = async <T>(
    domainId: number,
    action: (signal: AbortSignal) => Promise<T>,
    timeoutMessage = 'Authentication request timed out',
): Promise<T> => {
    const run = () => withAuthTimeout(action, timeoutMessage);
    if (typeof window === 'undefined' || typeof navigator === 'undefined' || !navigator.locks?.request) {
        return run();
    }

    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), AUTH_TIMEOUT);
    let lockAcquired = false;

    try {
        return await navigator.locks.request(
            `shopsys-auth-refresh-${domainId}`,
            { signal: controller.signal },
            async () => {
                lockAcquired = true;
                clearTimeout(timeout);

                return run();
            },
        );
    } catch (error) {
        if (lockAcquired) {
            throw error;
        }

        if (controller.signal.aborted) {
            // Bypassing a held lock could let a delayed refresh restore cookies after logout.
            throw new Error('Authentication lock timed out');
        }

        // Browsers that cannot use Web Locks retain the independent-request fallback.
        return run();
    } finally {
        clearTimeout(timeout);
    }
};
