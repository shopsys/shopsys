import { useEffect, useRef } from 'react';
import { isClient } from 'utils/isClient';

const callbacks = new Set<() => void>();

const handleVisibilityChange = () => {
    if (!document.hidden) {
        callbacks.forEach((cb) => cb());
    }
};

export const useRefetchOnTabFocus = (refetch: () => void): void => {
    const callbackRef = useRef(refetch);

    useEffect(() => {
        callbackRef.current = refetch;
    }, [refetch]);

    useEffect(() => {
        if (!isClient) {
            return undefined;
        }

        const callback = () => {
            callbackRef.current();
        };

        if (callbacks.size === 0) {
            document.addEventListener('visibilitychange', handleVisibilityChange);
        }

        callbacks.add(callback);

        return () => {
            callbacks.delete(callback);

            if (callbacks.size === 0) {
                document.removeEventListener('visibilitychange', handleVisibilityChange);
            }
        };
    }, []);
};
