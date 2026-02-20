import { Dispatch, SetStateAction, startTransition, useEffect } from 'react';

export const useScrollTop = (element: string, setTableStickyHeadActive: Dispatch<SetStateAction<boolean>>) => {
    useEffect(() => {
        const updateSize = () => {
            startTransition(() => {
                setTableStickyHeadActive(document.getElementById(element)!.getBoundingClientRect().top < -150);
            });
        };

        window.addEventListener('scroll', updateSize);
        updateSize();

        return () => {
            window.removeEventListener('scroll', updateSize);
        };
    }, [element, setTableStickyHeadActive]);
};
