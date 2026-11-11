import { useLogoutStore } from 'store/useLogoutStore';

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
    const getLogoutDeadline = () =>
        typeof window === 'undefined' ? undefined : useLogoutStore.getState().logoutDeadlineByDomain[domainId];
    const checkLogoutDeadline = () => {
        const deadline = getLogoutDeadline();
        if (deadline !== undefined && deadline <= Date.now()) {
            throw new Error('Authentication lock timed out');
        }
    };
    const run = () => {
        checkLogoutDeadline();

        return withAuthTimeout(action, timeoutMessage);
    };
    checkLogoutDeadline();
    if (typeof window === 'undefined' || typeof navigator === 'undefined' || !navigator.locks?.request) {
        return run();
    }

    const controller = new AbortController();
    const waitStarted = Date.now();
    let timeout: ReturnType<typeof setTimeout>;
    const checkWaitTimeout = () => {
        // A logout may start after an ordinary refresh has already queued for this lock.
        const remaining = (getLogoutDeadline() ?? waitStarted + AUTH_TIMEOUT) - Date.now();
        if (remaining > 0) {
            timeout = setTimeout(checkWaitTimeout, remaining);
        } else {
            controller.abort();
        }
    };
    timeout = setTimeout(checkWaitTimeout, AUTH_TIMEOUT);
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
