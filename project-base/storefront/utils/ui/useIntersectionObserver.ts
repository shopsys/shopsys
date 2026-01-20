import { RefObject, useEffect, useRef, useState } from 'react';

type UseIntersectionObserverOptions = {
    ref?: RefObject<HTMLElement | null>;
    enabled?: boolean;
    defaultIsIntersecting?: boolean;
};

export function useIntersectionObserver(options: UseIntersectionObserverOptions = {}) {
    const { ref: externalRef, enabled = true, defaultIsIntersecting = false } = options;

    const internalRef = useRef<HTMLDivElement | null>(null);
    const [isIntersecting, setIsIntersecting] = useState(defaultIsIntersecting);

    const targetRef = externalRef ?? internalRef;

    useEffect(() => {
        if (!enabled || !targetRef.current) {
            setIsIntersecting(false);
            return undefined;
        }

        const observer = new IntersectionObserver(([entry]) => setIsIntersecting(entry.isIntersecting));

        observer.observe(targetRef.current);

        return () => observer.disconnect();
    }, [enabled, targetRef]);

    return { ref: internalRef, isIntersecting };
}
