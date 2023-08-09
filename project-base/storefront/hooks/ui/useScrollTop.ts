import { canUseDom } from 'helpers/DOM/canUseDom';
import { useEffect, useState } from 'react';

export const useScrollTop = (element: string): number => {
    const [offsetTop, setOffsetTop] = useState(0);

    useEffect(() => {
        if (canUseDom()) {
            const updateSize = () => {
                setOffsetTop(document.getElementById(element)!.getBoundingClientRect().top);
            };
            window.addEventListener('scroll', updateSize);
            updateSize();
            return () => window.removeEventListener('scroll', updateSize);
        }
        return undefined;
    }, [element]);

    return offsetTop;
};
