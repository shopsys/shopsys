import { Dispatch, SetStateAction, startTransition, useEffect } from 'react';

export const useScrollTop = (element: string, setTableStickyHeadActive: Dispatch<SetStateAction<boolean>>) => {
    useEffect(() => {
        const el = document.getElementById(element);
        if (!el) {
            return undefined;
        }

        const observer = new IntersectionObserver(
            ([entry]) => {
                startTransition(() => {
                    setTableStickyHeadActive(!entry.isIntersecting && entry.boundingClientRect.top < 0);
                });
            },
            { rootMargin: '-150px 0px 0px 0px' },
        );

        observer.observe(el);

        return () => observer.disconnect();
    }, [element, setTableStickyHeadActive]);
};
