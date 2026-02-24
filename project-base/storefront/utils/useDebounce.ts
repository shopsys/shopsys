import { startTransition, useEffect, useState } from 'react';

export const useDebounce = <T>(value: T, delay?: number): T => {
    const [debouncedValue, setDebouncedValue] = useState<T>(value);

    useEffect(() => {
        const timer = setTimeout(() => {
            startTransition(() => {
                setDebouncedValue(value);
            });
        }, delay);

        return () => {
            clearTimeout(timer);
        };
    }, [value, delay]);

    return debouncedValue;
};
