import { useEffect, useRef } from 'react';
import { isClient } from 'utils/isClient';

const callbacks = new Set<() => void>();

const handleVisibilityChange = () => {
    if (!document.hidden) {
        callbacks.forEach((cb) => cb());
    }
};

export const useRefetchOnTabFocus = (refetch: () => void, includeWindowFocus = false): void => {
    const callbackRef = useRef(refetch);

    useEffect(() => {
        callbackRef.current = refetch;
    }, [refetch]);

    useEffect(() => {
        if (!isClient) {
            return undefined;
        }

        let timeout: ReturnType<typeof setTimeout> | undefined;
        const callback = () => {
            if (!includeWindowFocus) {
                callbackRef.current();
                return;
            }
            // Browsers may emit both visibilitychange and focus on the same return.
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                if (!document.hidden) callbackRef.current();
            }, 0);
        };
        const onWindowFocus = () => {
            if (!document.hidden) callback();
        };
        if (includeWindowFocus) window.addEventListener('focus', onWindowFocus);

        if (callbacks.size === 0) {
            document.addEventListener('visibilitychange', handleVisibilityChange);
        }

        callbacks.add(callback);

        return () => {
            clearTimeout(timeout);
            window.removeEventListener('focus', onWindowFocus);
            callbacks.delete(callback);

            if (callbacks.size === 0) {
                document.removeEventListener('visibilitychange', handleVisibilityChange);
            }
        };
    }, [includeWindowFocus]);
};
