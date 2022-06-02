import { RefObject, useEffect, useRef } from 'react';

export function useLatest<T>(current: T): RefObject<T> {
    const storedValue = useRef(current);

    useEffect(() => {
        storedValue.current = current;
    });

    return storedValue;
}
