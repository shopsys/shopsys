import { Dispatch, SetStateAction, useEffect } from 'react';

export const useScrollTop = (element: string, setTableStickyHeadActive: Dispatch<SetStateAction<boolean>>) => {
    useEffect(() => {
        const updateSize = () => {
            setTableStickyHeadActive(document.getElementById(element)!.getBoundingClientRect().top < -150);
        };

        window.addEventListener('scroll', updateSize);
        updateSize();

        return () => {
            window.removeEventListener('scroll', updateSize);
        };
    }, [element]);
};
