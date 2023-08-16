import { canUseDom } from 'helpers/canUseDom';
import { useEffect, useState } from 'react';

export const useGetWindowSize = (): { height: number; width: number } => {
    const [windowSize, setWindowSize] = useState({ height: -1, width: -1 });

    useEffect(() => {
        if (canUseDom()) {
            const updateSize = () => {
                setWindowSize({ height: window.innerHeight, width: window.innerWidth });
            };
            window.addEventListener('resize', updateSize);
            updateSize();
            return () => window.removeEventListener('resize', updateSize);
        }
        return undefined;
    }, []);

    return windowSize;
};
