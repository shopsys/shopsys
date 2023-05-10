import { DependencyList, useEffect, useRef } from 'react';

export const useComponentUpdate = (effect: () => void, deps: DependencyList | undefined): void => {
    const didMountRef = useRef(false);

    useEffect(() => {
        if (didMountRef.current) {
            effect();
        }
        didMountRef.current = true;
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, deps);
};
