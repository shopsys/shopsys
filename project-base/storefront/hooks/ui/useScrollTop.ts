import { useEffect, useState } from 'react';

export const useScrollTop = (element: string): number => {
    const [offsetTop, setOffsetTop] = useState(0);

    useEffect(() => {
        const updateSize = () => {
            setOffsetTop(document.getElementById(element)!.getBoundingClientRect().top);
        };
        window.addEventListener('scroll', updateSize);
        updateSize();
        return () => window.removeEventListener('scroll', updateSize);
    }, [element]);

    return offsetTop;
};
