import { Dispatch, SetStateAction, startTransition, useEffect } from 'react';

const OBSERVER_TOP_OFFSET = 150;

export const useScrollTop = (element: string, setTableStickyHeadActive: Dispatch<SetStateAction<boolean>>) => {
    useEffect(() => {
        const el = document.getElementById(element);
        if (!el) {
            return undefined;
        }

        const observer = new IntersectionObserver(
            ([entry]) => {
                startTransition(() => {
                    const observerTop = entry.rootBounds?.top ?? OBSERVER_TOP_OFFSET;

                    setTableStickyHeadActive(!entry.isIntersecting && entry.boundingClientRect.bottom <= observerTop);
                });
            },
            { rootMargin: `-${OBSERVER_TOP_OFFSET}px 0px 0px 0px` },
        );

        observer.observe(el);

        return () => observer.disconnect();
    }, [element, setTableStickyHeadActive]);
};
